<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler;

/**
 * Type cast to a string
 */
class StringHandler extends Handler
{
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return 'string';
    }
    
    /**
     * Cast value to a string
     *
     * @param mixed $value
     * @return string|mixed
     */
    public function cast($value)
    {
        $fn = 'cast' . ucfirst(gettype($value));
        
        return method_exists($this, $fn) ? $this->$fn($value) : (string)$value;
    }
    
    
    /**
     * Cast a resource to a string
     * 
     * @param resource $value
     * @return resource
     */
    protected function castResource($value)
    {
        return $this->dontCast($value);
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
    protected function arrayToString(): array
    {
        return $this->dontCastTo('string');
    }
}
