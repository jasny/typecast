<?php

namespace Jasny\TypeCast;

/**
 * Type cast to a object
 */
trait ToObject
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
     * Cast value to a object
     *
     * @return object|mixed
     */
    public function toObject()
    {
        $fn = gettype($this->getValue()) . 'ToObject';
        
        if (method_exists($this, $fn)) {
            $value = $this->$fn();
        } elseif (is_scalar($this->getValue())) {
            $value = $this->dontCastTo('object');
        } else {
            $value = (object)$this->getValue();
        }
        
        return $value;
    }
    
    
    /**
     * Cast null to a object
     * 
     * @return null
     */
    protected function nullToObject()
    {
        return null;
    }
    
    /**
     * Cast a resource to a object
     * 
     * @return resource
     */
    protected function resourceToObject()
    {
        return $this->dontCastTo('object');
    }
}
