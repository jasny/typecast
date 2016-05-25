<?php

namespace Jasny\TypeCast;

/**
 * Type cast to an object of a specific class
 */
trait ToClass
{
    /**
     * Trigger a warning that the value can't be casted and return $value
     * 
     * @param string $type
     * @param string $explain  Additional message
     * @return mixed
     */
    abstract public function dontCastTo($type, $explain = null);
    
    /**
     * Create a clone of this typecast object for a different value
     * 
     * @param mixed $value
     * @return static
     */
    protected function forValue($value);
    
    
    /**
     * Cast value to an object of a class
     *
     * @param string $class
     * @return object|mixed
     */
    public function toClass($class)
    {
        if (strtolower($class) === 'stdclass') {
            return $this->toStdClass();
        }

        if (is_object($this->value) && is_a($this->value, $class)) {
            return $this->value;
        }
        
        if (isset($this->value) && !class_exists($class)) {
            return $this->dontCastTo("$class object", "Class doesn't exist");
        }
        
        $fn = ($this->value instanceof \stdClass ? 'stdClass' : gettype($this->value)) . 'ToClass';
        return method_exists($this, $fn) ? $this->$fn($class) : new $class($this->value);
    }
    
    /**
     * Cast value to a stdClass object
     * 
     * @return object
     */
    protected function toStdClass()
    {
        if (is_object($this->value) && !$this->value instanceof \stdClass) {
            $array = get_object_vars($this->value);
            $cast = $this->forValue($array);
        } else {
            $cast = $this;
        }
        
        return $cast->toObject();
    }
    
    
    /**
     * Cast null to a class
     * 
     * @param string $class
     * @return null
     */
    protected function nullToClass($class)
    {
        return null;
    }
    
    /**
     * Cast an array to a class
     * 
     * @param string $class
     * @return null
     */
    protected function arrayToClass($class)
    {
        if (method_exists($class, '__set_state')) {
            $object = $class::__set_state($this->value);
        } else {
            $object = new $class($this->value);
        }
        
        return $object;
    }
    
    /**
     * Cast a stdClass object to a class
     * 
     * @param string $class
     * @return null
     */
    protected function stdClassToClass($class)
    {
        $array = get_object_vars($this->value);
        return $this->forValue($array)->arrayToClass($class);
    }
}
