<?php

namespace Jasny\TypeCast;

/**
 * Type cast to an integer or float
 */
trait ToNumber
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
     * Cast value to an integer
     *
     * @return int|mixed
     */
    public function toInteger()
    {
        return $this->toNumber('integer', $this->value);
    }
    
    /**
     * Cast value to an integer
     *
     * @return float|mixed
     */
    public function toFloat()
    {
        return $this->toNumber('float', $this->value);
    }
    
    /**
     * Cast value to an integer
     *
     * @param string $type  'integer' or 'float'
     * @return int|float|mixed
     */
    protected function toNumber($type)
    {
        $fn = gettype($this->value) . 'ToNumber';
        
        if (method_exists($this, $fn)) {
            $value = $this->$fn($type);
        } else {
            $value = $this->value;
            settype($value, $type);
        }

        return $value;
    }
    
    /**
     * Cast null to number
     * 
     * @param string $type  'integer' or 'float'
     * @return null
     */
    protected function nullToNumber($type)
    {
        return null;
    }
    
    /**
     * Cast a resource to a number
     * 
     * @param string $type  'integer' or 'float'
     * @return resource
     */
    protected function resourceToNumber($type)
    {
        return $this->dontCastTo($type);
    }
    
    /**
     * Cast an object to a number
     * 
     * @param string $type  'integer' or 'float'
     * @return object
     */
    protected function objectToNumber($type)
    {
        return $this->dontCastTo($type);
    }
    
    /**
     * Cast an array to a number
     * 
     * @param string $type  'integer' or 'float'
     * @return array
     */
    protected function arrayToNumber($type)
    {
        return $this->dontCastTo($type);
    }

    /**
     * Cast a string to a number
     * 
     * @param string $type  'integer' or 'float'
     * @return number
     */
    protected function stringToNumber($type)
    {
        $value = trim($this->value);
    
        if (!is_numeric($value) && $value !== '') {
            return $this->dontCastTo($type);
        }
        
        settype($value, $type);
        return $value;
    }
}
