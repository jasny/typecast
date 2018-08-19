<?php

namespace Jasny;

use Jasny\TypeCast\Handler;

/**
 * Interface for type casters
 */
interface TypeCastInterface
{
    /**
     * Add a custom alias
     * 
     * @param string $alias
     * @param string $type
     * @return static
     */
    public function alias(string $alias, string $type): self;
    
    /**
     * Get handler to cast value to specified type
     *
     * @param string $type
     * @return Handler
     */
    public function to(string $type);
}
