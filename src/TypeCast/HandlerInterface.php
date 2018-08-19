<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;

/**
 * Handle the typecasting
 */
interface HandlerInterface
{
    /**
     * Set the warning level or throwable when variable can't be cased to type.
     * 
     * @param int|string $level  E_* or Throwable class name
     * @return static
     */
    public function onFailure($level);
    
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
