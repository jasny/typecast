<?php

namespace Jasny\TypeCast;

/**
 * Type cast to a boolean
 */
trait ToBoolean
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
     * Cast value to a boolean
     *
     * @return boolean|mixed
     */
    public function toBoolean()
    {
        $fn = gettype($this->getValue()) . 'ToBoolean';
        
        return method_exists($this, $fn) ? $this->$fn() : (boolean)$this->getValue();
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
    protected function arrayToBoolean(): array
    {
        return $this->dontCastTo('boolean');
    }

    /**
     * Cast a string to a boolean
     * 
     * @return boolean|string
     */
    protected function stringToBoolean()
    {
        $string = strtolower(trim($this->getValue()));

        if (in_array($string, $this->getBooleanStrings(true))) {
            return true;
        }

        if (in_array($string, $this->getBooleanStrings(false))) {
            return false;
        }

        return $this->dontCastTo('boolean');
    }
    
    /**
     * Get strings that represent true or false
     * 
     * @param boolean|null $state
     * @return array
     */
    protected function getBooleanStrings($state = null): array
    {
        $strings = [
            false => ['', '0', 'false', 'no', 'off'],
            true => ['1', 'true', 'yes', 'on']
        ];
        
        return isset($state) ? $strings[$state] : array_merge($strings[false], $strings[true]);
    }
}
