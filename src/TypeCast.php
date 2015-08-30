<?php

namespace Jasny;

/**
 * Class for type casting
 * 
 * ```
 * $string = TypeCast::cast($myValue, 'string');
 * 
 * $string = TypeCast::value($myValue)->to('string');
 * $foo = TypeCast::value($data)->to('Foo');
 * ```
 */
class TypeCast
{
    /**
     * @var mixed
     */
    protected $value;
    
    /**
     * Class constructor
     * 
     * @param mixed $value
     */
    protected function __construct($value)
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
        return new self($value);
    }
    
    /**
     * Cast value
     * 
     * @param string $type
     * @return mixed
     */
    public function to($type)
    {
        return static::cast($this->value, $type);
    }
    
    
    /**
     * Cast the value to a type.
     * 
     * @param mixed  $value
     * @param string $type
     * @return mixed
     */
    public static function cast($value, $type)
    {
        if ($type === 'bool') $type = 'boolean';
        if ($type === 'int') $type = 'integer';
        
        // Cast internal types
        if (in_array($type, ['string', 'boolean', 'integer', 'float', 'array', 'object', 'resource'])) {
            return call_user_func([get_called_class(), 'to' . ucfirst($type)], $value);
        }

        // Cast to class
        return substr($type, -2) === '[]' ?
            static::toArray($value, substr($type, 0, -2)) :
            static::toClass($value, $type);
    }
    
    /**
     * Cast value to a string
     * 
     * @param mixed $value
     * @return string
     */
    public static function toString($value)
    {
        if (is_null($value)) return null;
    
        if ($value instanceof \DateTime) return $value->format('c');

        if (is_resource($value)) {
            trigger_error("Unable to cast a " . get_resource_type($value) . " resource to a string", E_USER_WARNING);
            return $value;
        }
        
        if (is_array($value)) {
            trigger_error("Unable to cast an array to a string", E_USER_WARNING);
            return $value;
        }
        
        if (is_object($value) && !method_exists($value, '__toString')) {
            trigger_error("Unable to cast a " . get_class($value).  " object to a string", E_USER_WARNING);
            return $value;
        }
        
        return (string)$value;
    }
    
    /**
     * Cast value to a boolean
     * 
     * @param mixed $value
     * @return boolean
     */
    public static function toBoolean($value)
    {
        if (is_null($value)) return null;
    
        if (is_resource($value)) {
            trigger_error("Unable to cast a " . get_resource_type($value) . " resource to a boolean", E_USER_WARNING);
            return $value;
        }
        
        if (is_object($value)) {
            trigger_error("Unable to cast a " . get_class($value) . " object to a boolean", E_USER_WARNING);
            return $value;
        }
        
        if (is_array($value)) {
            trigger_error("Unable to cast an array to a boolean", E_USER_WARNING);
            return $value;
        }
        
        if (is_string($value)) {
            if (in_array(strtolower($value), ['1', 'true', 'yes', 'on'])) return true;
            if (in_array(strtolower($value), ['', '0', 'false', 'no', 'off'])) return false;
            
            trigger_error("Unable to cast string \"$value\" to a boolean", E_USER_WARNING);
            return $value;
        }
        
        return (bool)$value;
    }
    
    /**
     * Cast value to an integer
     * 
     * @param mixed $value
     * @return int
     */
    public static function toInteger($value)
    {
        return static::toNumber('integer', $value);
    }
    
    /**
     * Cast value to an integer
     * 
     * @param mixed $value
     * @return int
     */
    public static function toFloat($value)
    {
        return static::toNumber('float', $value);
    }
    
    /**
     * Cast value to an integer
     * 
     * @param string $type   'integer' or 'float'
     * @param mixed  $value
     * @return int|float
     */
    protected static function toNumber($type, $value)
    {
        if (is_null($value)) return null;
    
        if (is_resource($value)) {
            trigger_error("Unable to cast a " . get_resource_type($value) . " resource to a $type", E_USER_WARNING);
            return $value;
        }
        
        if (is_object($value)) {
            trigger_error("Unable to cast a " . get_class($value) . " object to a $type", E_USER_WARNING);
            return $value;
        }
        
        if (is_array($value)) {
            trigger_error("Unable to cast an array to a $type", E_USER_WARNING);
            return $value;
        }
        
        if (is_string($value)) {
            $value = trim($value);
            if (!is_numeric(trim($value)) && $value !== '') {
                trigger_error("Unable to cast string \"$value\" to a $type", E_USER_WARNING);
                return $value;
            }
        }
        
        settype($value, $type);
        return $value;
    }

    /**
     * Cast value to a typed array
     * 
     * @param mixed  $value
     * @param string $subtype  Type of the array items
     * @return mixed
     */
    public static function toArray($value, $subtype = null)
    {
        if (is_null($value)) return null;
    
        if (is_resource($value)) {
            trigger_error("Unable to cast a " . get_resource_type($value) . " resource to an array", E_USER_WARNING);
            return $value;
        }
        
        $array = $value === '' ? [] : (array)$value;

        if (isset($subtype)) {
            foreach ($array as &$v) {
                $v = static::cast($v, $subtype);
            }
        }
        
        return $array;
    }
    
    /**
     * Cast value to an object
     * 
     * @param mixed $value
     * @return object
     */
    public static function toObject($value)
    {
        if (is_null($value)) return null;
    
        if (is_resource($value)) {
            trigger_error("Unable to cast a " . get_resource_type($value) . " resource to an object", E_USER_WARNING);
            return $value;
        }
        
        if (is_scalar($value)) {
            trigger_error("Unable to cast a ". gettype($value) . " to an object.", E_USER_WARNING);
            return $value;
        }
        
        return (object)$value;
    }
    
    /**
     * Cast value to a resource
     * 
     * @param mixed $value
     * @return object
     */
    public static function toResource($value)
    {
        if (is_null($value)) return null;
    
        if (is_resource($value)) return $value;
    
        trigger_error("Unable to cast a ". gettype($value) . " to a resource.", E_USER_WARNING);
        return $value;
    }
    
    /**
     * Cast value to a non-internal type
     * 
     * @param mixed  $value
     * @param string $class
     * @return object
     */
    public static function toClass($value, $class)
    {
        if (is_null($value)) return null;
        
        if (is_object($value) && is_a($value, $class)) return $value;
        
        if (!class_exists($class)) throw new \Exception("Invalid type '$class'");
        return new $class($value);
    }
}
