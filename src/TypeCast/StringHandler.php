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
     * @param mixed $value
     * @return object
     */
    protected function castObject($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('c');
        }
        
        if (method_exists($value, '__toString')) {
            return (string)$value;
        }
        
        return $this->dontCast($value);
    }
    
    /**
     * Cast an array to a string
     * 
     * @param mixed $value
     * @return array
     */
    protected function castArray($value): array
    {
        return $this->dontCast($value);
    }
}
