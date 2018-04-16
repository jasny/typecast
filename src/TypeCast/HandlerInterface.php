<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;

/**
 * Handle the typecasting
 */
interface HandlerInterface
{
    /**
     * Use handler to cast to type.
     * 
     * @param string $type
     * @return HandlerInterface
     * @throws \LogicException if handler can't be used
     */
    public function forType(string $type): self;
    
    /**
     * Set typecast
     * 
     * @param TypeCastInterface $typecast
     * @return HandlerInterface
     */
    public function usingTypecast(TypeCastInterface $typecast): self;
    
    
    /**
     * Cast value
     * 
     * @param mixed $value
     * @return mixed
     */
    public function cast($value);
}
