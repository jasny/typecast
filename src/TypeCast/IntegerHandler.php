<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\NumberHandler;

/**
 * Cast to an integer
 */
class IntegerHandler extends NumberHandler
{
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return 'integer';
    }
}
