<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler;

/**
 * Type cast to an integer or float
 */
abstract class NumberHandler extends Handler
{
    /**
     * Cast value to an integer
     *
     * @param mixed $value
     * @return int|float|mixed
     */
    public function cast($value)
    {
        $fn = 'cast' . ucfirst(gettype($value));
        
        if (method_exists($this, $fn)) {
            $value = $this->$fn($value);
        } else {
            settype($value, $this->getType());
        }

        return $value;
    }
    
    /**
     * Cast a resource to a number
     * 
     * @param resource $value
     * @return resource
     */
    protected function castResource($value)
    {
        return $this->dontCast($value);
    }
    
    /**
     * Cast an object to a number
     * 
     * @param object $value
     * @return object
     */
    protected function castObject($value)
    {
        return $this->dontCast($value);
    }
    
    /**
     * Cast an array to a number
     * 
     * @param array $value
     * @return array
     */
    protected function castArray(array $value): array
    {
        return $this->dontCast($value);
    }

    /**
     * Cast a string to a number
     * 
     * @param string $value
     * @return int|float|string
     */
    protected function castString(string $value)
    {
        $val = trim($value);
    
        if (!is_numeric($val) && $val !== '') {
            return $this->dontCast($val);
        }
        
        settype($val, $this->getType());
        
        return $val;
    }
}
