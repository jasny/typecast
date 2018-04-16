<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\NumberHandler;

/**
 * Cast to a float
 */
class FloatHandler extends NumberHandler
{
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return 'float';
    }
}
