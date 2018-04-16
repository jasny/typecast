<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler;

/**
 * Type cast to mixed (no casting)
 */
class MixedHandler extends Handler
{
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return 'mixed';
    }
    
    /**
     * Leave value as is
     * 
     * @return mixed
     */
    public function cast($value)
    {
        return $value;
    }
}
