<?php

namespace Jasny\TypeCast;

/**
 * Type cast to a resource
 */
trait ToResource
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
     * Cast value to a resource
     *
     * @return resource|mixed
     */
    public function toResource()
    {
        $fn = gettype($this->value) . 'ToResource';
        
        if (method_exists($this, $fn)) {
            $value = $this->$fn();
        } elseif (!is_resource($this->value)) {
            $value = $this->dontCastTo('resource');
        } else {
            $value = $this->value;
        }
        
        return $value;
    }
    
    
    /**
     * Cast null to a resource
     * 
     * @return null
     */
    protected function nullToResource()
    {
        return null;
    }
}
