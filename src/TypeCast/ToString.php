<?php

namespace Jasny\TypeCast;

/**
 * Type cast to a string
 */
trait ToString
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
    abstract public function dontCastTo($type, $explain = null);
    
    /**
     * Cast value to a string
     *
     * @return string|mixed
     */
    public function toString()
    {
        $fn = gettype($this->getValue()) . 'ToString';
        return method_exists($this, $fn) ? $this->$fn() : (string)$this->getValue();
    }
    
    
    /**
     * Cast null to a string
     * 
     * @return null
     */
    protected function nullToString()
    {
        return null;
    }
    
    /**
     * Cast a resource to a string
     * 
     * @return resource
     */
    protected function resourceToString()
    {
        return $this->dontCastTo('string');
    }
    
    /**
     * Cast an object to a string
     * 
     * @return object
     */
    protected function objectToString()
    {
        if ($this->getValue() instanceof \DateTime) {
            $value = $this->getValue()->format('c');
        } elseif (method_exists($this->getValue(), '__toString')) {
            $value = (string)$this->getValue();
        } else {
            $value = $this->dontCastTo('string');
        }
        
        return $value;
    }
    
    /**
     * Cast an array to a string
     * 
     * @return array
     */
    protected function arrayToString()
    {
        return $this->dontCastTo('string');
    }
}
