<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;

/**
 * Type cast to an object of a specific class
 */
trait ToClass
{
    /**
     * Get the value
     * 
     * @return mixed
     */
    abstract public function getValue();
    
    /**
     * Trigger a warning that the value can't be casted and return $value
     * 
     * @param string $type
     * @param string $explain  Additional message
     * @return mixed
     */
    abstract public function dontCastTo(string $type, string $explain = null);
    
    /**
     * Cast value
     *
     * @param string $type
     * @return mixed
     */
    abstract public function to(string $type);
    
    /**
     * Create a clone of this typecast object for a different value
     * 
     * @param mixed $value
     * @return TypeCastInterface|static
     */
    abstract public function forValue($value): TypeCastInterface;
    
    
    /**
     * Cast value to an object of a class
     *
     * @param string $class
     * @return object|mixed
     */
    public function toClass(string $class)
    {
        if (strtolower($class) === 'stdclass') {
            return $this->toStdClass();
        }

        if (is_object($this->getValue()) && is_a($this->getValue(), $class)) {
            return $this->getValue();
        }
        
        if ($this->getValue() !== null && !class_exists($class)) {
            return $this->dontCastTo("$class object", "Class doesn't exist");
        }
        
        $fn = ($this->getValue() instanceof \stdClass ? 'stdClass' : gettype($this->getValue())) . 'ToClass';
        return method_exists($this, $fn) ? $this->$fn($class) : new $class($this->getValue());
    }
    
    /**
     * Cast value to a stdClass object
     * 
     * @return object
     */
    protected function toStdClass()
    {
        if (is_object($this->getValue()) && !$this->getValue() instanceof \stdClass) {
            $array = get_object_vars($this->getValue());
            $cast = $this->forValue($array);
        } else {
            $cast = $this;
        }
        
        return $cast->to('object');
    }
    
    
    /**
     * Cast null to a class
     * 
     * @return null
     */
    protected function nullToClass()
    {
        return null;
    }
    
    /**
     * Cast an array to a class
     * 
     * @param string $class
     * @return object|null
     */
    protected function arrayToClass(string $class)
    {
        if (method_exists($class, '__set_state')) {
            $object = $class::__set_state($this->getValue());
        } else {
            $object = new $class($this->getValue());
        }
        
        return $object;
    }
    
    /**
     * Cast a stdClass object to a class
     * 
     * @param string $class
     * @return object|null
     */
    protected function stdClassToClass(string $class)
    {
        $array = get_object_vars($this->getValue());
        
        return $this->forValue($array)->arrayToClass($class);
    }
}
