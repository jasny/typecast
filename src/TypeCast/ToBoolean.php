<?php

namespace Jasny\TypeCast;

/**
 * Type cast to a boolean
 */
trait ToBoolean
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
     * Cast value to a boolean
     *
     * @return boolean|mixed
     */
    public function toBoolean()
    {
        $fn = gettype($this->value) . 'ToBoolean';
        return method_exists($this, $fn) ? $this->$fn() : (boolean)$this->value;
    }
        
    /**
     * Cast null to boolean
     * 
     * @return null
     */
    protected function nullToBoolean()
    {
        return null;
    }
    
    /**
     * Cast a resource to a boolean
     * 
     * @return resource
     */
    protected function resourceToBoolean()
    {
        return $this->dontCastTo('boolean');
    }
    
    /**
     * Cast an object to a boolean
     * 
     * @return object
     */
    protected function objectToBoolean()
    {
        return $this->dontCastTo('boolean');
    }
    
    /**
     * Cast an array to a boolean
     * 
     * @return array
     */
    protected function arrayToBoolean()
    {
        return $this->dontCastTo('boolean');
    }

    /**
     * Cast a string to a boolean
     * 
     * @return boolean
     */
    protected function stringToBoolean()
    {
        $string = strtolower(trim($this->value));

        if (in_array($string, ['1', 'true', 'yes', 'on'])) {
            return true;
        }

        if (in_array($string, ['', '0', 'false', 'no', 'off'])) {
            return false;
        }

        return $this->dontCastTo('boolean');
    }
}
