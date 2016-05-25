<?php

namespace Jasny\TypeCast;

/**
 * Type cast to mixed (no casting)
 */
trait ToMixed
{
    /**
     * Leave value as is
     * 
     * @return mixed
     */
    public function toMixed()
    {
        return $this->value;
    }
}
