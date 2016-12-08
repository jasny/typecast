<?php

namespace Jasny;

use Jasny\TypeCast;

/**
 * Class for type casting
 *
 *     $string = TypeCast::value($myValue)->to('string');
 *     $foo = TypeCast::value($data)->to('Foo');
 * 
 * When casting to an object of a class, the `__set_state()` method is used if available and the value is an array or a
 * stdClass object.
 */
class TypeCast
{
    use TypeCast\ToMixed,
        TypeCast\ToNumber,
        TypeCast\ToString,
        TypeCast\ToBoolean,
        TypeCast\ToArray,
        TypeCast\ToObject,
        TypeCast\ToResource,
        TypeCast\ToClass,
        TypeCast\ToMultiple;
    
    /**
     * @var mixed
     */
    protected $value;
    
    /**
     * The display name
     * @var string
     */
    protected $name;
    
    /**
     * Type aliases
     * @var string[]
     */
    protected $aliases = [
        'bool' => 'boolean',
        'int' => 'integer',
        'mixed[]' => 'array'
    ];
    
    
    /**
     * Class constructor
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    /**
     * Factory method
     *
     * @param mixed $value
     */
    public static function value($value)
    {
        return new static($value);
    }
    
    /**
     * Create a clone of this typecast object for a different value
     * 
     * @param mixed $value
     * @return static
     */
    protected function forValue($value)
    {
        $cast = clone $this;
        $cast->value = $value;
        
        return $cast;
    }
    
    
    /**
     * Get the value
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Set the display name.
     * This is used in notices.
     * 
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Add a custom alias
     * 
     * @param string $alias
     * @param string $type
     */
    public function alias($alias, $type)
    {
        $this->aliases[$alias] = $type;
    }

    /**
     * Replace alias type with full type
     * 
     * @param string $type
     */
    public function normalizeType(&$type)
    {
        if (substr($type, -2) === '[]') {
            $subtype = substr($type, 0, -2);
            $this->normalizeType($subtype);
            
            $type = $subtype . '[]';
            return;
        }
        
        if (isset($this->aliases[$type])) {
            $type = $this->aliases[$type];
        }
    }
    
    /**
     * Get the internal types
     * 
     * @return array
     */
    protected function getInternalTypes()
    {
        return ['string', 'boolean', 'integer', 'float', 'array', 'object', 'resource', 'mixed'];
    }
    
    /**
     * Cast value
     *
     * @param string $type
     * @return mixed
     */
    public function to($type)
    {
        if (strstr($type, '|')) {
            return $this->toMultiple(explode('|', $type));
        }
        
        $this->normalizeType($type);
        
        // Cast internal types
        if (in_array($type, $this->getInternalTypes())) {
            return call_user_func([$this, 'to' . ucfirst($type)]);
        }

        // Cast to class
        return substr($type, -2) === '[]'
            ? $this->toArray(substr($type, 0, -2))
            : $this->toClass($type);
    }
    

    /**
     * Get a descript of the type of the value
     *
     * @return string
     */
    protected function getValueTypeDescription()
    {
        if (is_resource($this->getValue())) {
            $valueType = "a " . get_resource_type($this->getValue()) . " resource";
        } elseif (is_array($this->getValue())) {
            $valueType = "an array";
        } elseif (is_object($this->getValue())) {
            $valueType = "a " . get_class($this->getValue()) . " object";
        } elseif (is_string($this->getValue())) {
            $valueType = "string \"{$this->getValue()}\"";
        } else {
            $valueType = "a " . gettype($this->getValue());
        }

        return $valueType;
    }
    
    /**
     * Trigger a warning that the value can't be casted and return $value
     * 
     * @param string $type
     * @param string $explain  Additional message
     * @return mixed
     */
    public function dontCastTo($type, $explain = null)
    {
        $valueType = $this->getValueTypeDescription();
        
        if (!strstr($type, '|')) {
            $type = (in_array($type, ['array', 'object']) ? 'an ' : 'a ') . $type;
        }
        
        $name = isset($this->name) ? " {$this->name} from" : '';
        
        $message = "Unable to cast" . $name . " $valueType to $type" . (isset($explain) ? ": $explain" : '');
        trigger_error($message, E_USER_NOTICE);
        
        return $this->getValue();
    }
}
