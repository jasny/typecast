<?php

namespace Jasny;

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
    /**
     * @var mixed
     */
    protected $value;
    
    /**
     * Type aliases
     * @var string[]
     */
    protected $aliases = [
        'bool' => 'boolean',
        'int' => 'integer'
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
     * Check that all items of value are of a specific type
     * 
     * @param array $types
     */
    protected function allSubValuesAre($types)
    {
        foreach ($this->value as $item) {
            $compare = function ($type) use ($item) {
                return gettype($item) === $type || is_a($item, $type);
            };
                
            if (max(array_map($compare, $types)) !== true) {
                return false;
            }
        }
        
        return true;
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
            return $this->toMultiType(explode('|', $type));
        }
        
        $this->normalizeType($type);
        
        // Cast internal types
        if (in_array($type, ['string', 'boolean', 'integer', 'float', 'array', 'object', 'resource', 'mixed'])) {
            return call_user_func([$this, 'to' . ucfirst($type)]);
        }

        // Cast to class
        return substr($type, -2) === '[]'
            ? $this->toArray(substr($type, 0, -2))
            : $this->toClass($type);
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
        if (is_resource($this->value)) {
            $valueType = "a " . get_resource_type($this->value) . " resource";
        } elseif (is_array($this->value)) {
            $valueType = "an array";
        } elseif (is_object($this->value)) {
            $valueType = "a " . get_class($this->value) . " object";
        } elseif (is_string($this->value)) {
            $valueType = "string \"{$this->value}\"";
        } else {
            $valueType = "a " . gettype($this->value);
        }
        
        if (!strstr($type, '|')) {
            $type = (in_array($type, ['array', 'object']) ? 'an ' : 'a ') . $type;
        }
        
        $message = "Unable to cast $valueType to $type" . (isset($explain) ? ": $explain" : '');
        trigger_error($message, E_USER_WARNING);
        
        return $this->value;
    }
    
    
    /**
     * Leave value as is
     * 
     * @return mixed
     */
    public function toMixed()
    {
        return $this->value;
    }
    
    /**
     * Check if value is one of the types, otherwise trigger a warning
     * 
     * @param array $types
     * @return mixed
     */
    public function toMultiType($types)
    {
        $types = array_diff($types, ['null']);
        if (count($types) === 1) {
            return $this->to(reset($types));
        }
        
        $valueType = gettype($this->value);
        
        $found = false;
        $subtypes = [];
        
        foreach ($types as &$type) {
            $this->normalizeType($type);

            if ($type === $valueType || is_a($this->value, $type)) {
                $found = true;
                break;
            }
            
            if (substr($type, -2) === '[]') {
                $subtypes[] = substr($type, 0, -2);
            }
        }
        
        if (!$found && is_array($this->value) && !empty($subtypes)) {
            if (count($subtypes) === 1) {
                return $this->toArray($subtypes[0]);
            }
            
            $found = $this->allSubValuesAre($subtypes);
        }
        
        return $found
            ? $this->value
            : $this->dontCastTo(join('|', $types));
    }
    
    /**
     * Cast value to a string
     *
     * @return string
     */
    public function toString()
    {
        if (is_null($this->value)) {
            return null;
        }
        
        if ($this->value instanceof \DateTime) {
            return $this->value->format('c');
        }

        if (
            is_resource($this->value) ||
            is_array($this->value) ||
            (is_object($this->value) && !method_exists($this->value, '__toString')))
        {
            return $this->dontCastTo('string');
        }
        
        return (string)$this->value;
    }
    
    /**
     * Cast value to a boolean
     *
     * @return boolean
     */
    public function toBoolean()
    {
        if (is_null($this->value)) {
            return null;
        }
        
        if (is_resource($this->value) || is_object($this->value) || is_array($this->value)) {
            return $this->dontCastTo('boolean');
        }
        
        if (is_string($this->value)) {
            $string = strtolower(trim($this->value));
            
            if (in_array($string, ['1', 'true', 'yes', 'on'])) {
                return true;
            }
            
            if (in_array($string, ['', '0', 'false', 'no', 'off'])) {
                return false;
            }
            
            return $this->dontCastTo('boolean');
        }
        
        return (boolean)$this->value;
    }
    
    /**
     * Cast value to an integer
     *
     * @return int
     */
    public function toInteger()
    {
        return $this->toNumber('integer', $this->value);
    }
    
    /**
     * Cast value to an integer
     *
     * @return int
     */
    public function toFloat()
    {
        return $this->toNumber('float', $this->value);
    }
    
    /**
     * Cast value to an integer
     *
     * @param string $type   'integer' or 'float'
     * @return int|float
     */
    protected function toNumber($type)
    {
        if (is_null($this->value)) {
            return null;
        }
        
        if (is_resource($this->value) || is_object($this->value) || is_array($this->value)) {
            return $this->dontCastTo($type);
        }
        
        if (is_string($this->value)) {
            $value = trim($this->value);
            
            if (!is_numeric($value) && $value !== '') {
                return $this->dontCastTo($type);
            }
        } else {
            $value = $this->value;
        }
        
        settype($value, $type);
        return $value;
    }

    /**
     * Cast value to a typed array
     *
     * @param string|\Closure $subtype  Type of the array items
     * @return mixed
     */
    public function toArray($subtype = null)
    {
        if (is_null($this->value)) {
            return null;
        }
        
        if (is_resource($this->value)) {
            return $this->dontCastTo('array');
        }
        
        if (is_object($this->value)) {
            $array = $this->value instanceof \StdClass
                ? call_user_func('get_object_vars', $this->value)
                : [$this->value];
        } else {
            $array = $this->value === '' ? [] : (array)$this->value;
        }

        if (isset($subtype)) {
            foreach ($array as &$item) {
                if ($subtype instanceof \Closure) {
                    $item = $subtype($item);
                } else {
                    $item = $this->forValue($item)->to($subtype);
                }
            }
        }
        
        return $array;
    }
    
    /**
     * Cast value to an object
     *
     * @return object
     */
    public function toObject()
    {
        if (is_null($this->value)) {
            return null;
        }
        
        if (is_resource($this->value) || is_scalar($this->value)) {
            return $this->dontCastTo('object');
        }
        
        return (object)$this->value;
    }
    
    /**
     * Cast value to a resource
     *
     * @return resource
     */
    public function toResource()
    {
        if (is_null($this->value)) {
            return null;
        }
        
        if (is_resource($this->value)) {
            return $this->value;
        }
        
        return $this->dontCastTo('resource');
    }
    
    /**
     * Cast value to an object of a class
     *
     * @param string $class
     * @return object
     */
    public function toClass($class)
    {
        if (is_null($this->value)) {
            return null;
        }
        
        if (is_object($this->value) && is_a($this->value, $class)) {
            return $this->value;
        }
        
        if (strtolower($class) === 'stdclass') {
            return $this->toStdClass();
        }
        
        if (!class_exists($class)) {
            return $this->dontCastTo("$class object", "Class not found");
        }
        
        if ((is_array($this->value) || $this->value instanceof stdClass) && method_exists($class, '__set_state')) {
            $array = is_object($this->value) ? call_user_func('get_object_vars', $this->value) : $this->value;
            return $class::__set_state($array);
        }
        
        return new $class($this->value);
    }
    
    /**
     * Cast value to a stdClass object
     * 
     * @return object
     */
    protected function toStdClass()
    {
        if (is_object($this->value)) {
            $array = call_user_func('get_object_vars', $this->value);
            $cast = $this->forValue($array);
        } else {
            $cast = $this;
        }
        
        return $cast->toObject();            
    }
}
