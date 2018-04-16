<?php

namespace Jasny\TypeCast;

/**
 * Type cast to an integer or float
 */
trait ToNumber
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
     * Cast value to an integer
     *
     * @return int|mixed
     */
    public function toInteger()
    {
        return $this->toNumber('integer');
    }
    
    /**
     * Cast value to an integer
     *
     * @return float|mixed
     */
    public function toFloat()
    {
        return $this->toNumber('float');
    }
    
    /**
     * Cast value to an integer
     *
     * @param string $type  'integer' or 'float'
     * @return int|float|mixed
     */
    protected function toNumber(string $type)
    {
        $fn = gettype($this->getValue()) . 'ToNumber';
        
        if (method_exists($this, $fn)) {
            $value = $this->$fn($type);
        } else {
            $value = $this->getValue();
            settype($value, $type);
        }

        return $value;
    }
    
    /**
     * Cast null to number
     * 
     * @return null
     */
    protected function nullToNumber()
    {
        return null;
    }
    
    /**
     * Cast a resource to a number
     * 
     * @param string $type  'integer' or 'float'
     * @return resource
     */
    protected function resourceToNumber(string $type)
    {
        return $this->dontCastTo($type);
    }
    
    /**
     * Cast an object to a number
     * 
     * @param string $type  'integer' or 'float'
     * @return object
     */
    protected function objectToNumber(string $type)
    {
        return $this->dontCastTo($type);
    }
    
    /**
     * Cast an array to a number
     * 
     * @param string $type  'integer' or 'float'
     * @return array
     */
    protected function arrayToNumber(string $type): array
    {
        return $this->dontCastTo($type);
    }

    /**
     * Cast a string to a number
     * 
     * @param string $type  'integer' or 'float'
     * @return int|float|string
     */
    protected function stringToNumber(string $type)
    {
        $value = trim($this->getValue());
    
        if (!is_numeric($value) && $value !== '') {
            return $this->dontCastTo($type);
        }
        
        settype($value, $type);
        return $value;
    }
}
