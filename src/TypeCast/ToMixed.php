<?php

namespace Jasny\TypeCast;

/**
 * Type cast to mixed (no casting)
 */
trait ToMixed
{
    /**
     * Get the value
     * 
     * @return mixed
     */
    abstract public function getValue();
    
    /**
     * Leave value as is
     * 
     * @return mixed
     */
    public function toMixed()
    {
        return $this->getValue();
    }
}
