<?php

namespace Jasny\TypeCast\Handler;

use Jasny\TypeCastInterface;
use Jasny\TypeCast\Handler;
use Jasny\TypeCast\HandlerInterface;
use Jasny\TypeCast\TypeGuess;
use Jasny\TypeCast\TypeGuessInterface;
use LogicException;
use Traversable;

/**
 * Cast value to one of multiple types
 */
class MultipleHandler extends Handler
{
    /**
     * @var TypeGuess
     */
    protected $typeGuess;
    
    /**
     * @var TypeCastInterface 
     */
    protected $typecast;
    
    /**
     * Possible types
     * @var array
     */
    protected $types = [];
    
    
    /**
     * Class constructor
     *
     * @param TypeGuessInterface $typeGuess
     */
    public function __construct(TypeGuessInterface $typeGuess = null)
    {
        $this->typeGuess = $typeGuess ?? new TypeGuess();
    }

    /**
     * Use handler to cast to type.
     * 
     * @param string $type
     * @return static
     */
    public function forType(string $type): HandlerInterface
    {
        return $this->forTypes(explode('|', $type));
    }

    /**
     * Use handler to cast to type.
     * 
     * @param string[] $types
     * @return static
     */
    public function forTypes(array $types): HandlerInterface
    {
        $unique = array_unique($types);
        
        if (count($unique) === count($this->types) && count(array_udiff($unique, $this->types, 'strcasecmp')) === 0) {
            return $this;
        }
        
        $handler = clone $this;
        $handler->types = $unique;
        
        return $handler;
    }
    
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return join('|', $this->types);
    }
    
    /**
     * Set typecast
     * 
     * @param TypeCastInterface $typecast
     * @return static
     */
    public function usingTypecast(TypeCastInterface $typecast): HandlerInterface
    {
        $handler = clone $this;
        $handler->typecast = $typecast;
        
        return $handler;
    }

    
    /**
     * Cast the value to one of the types.
     * 
     * @param mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        if (!isset($this->typecast)) {
            throw new LogicException("Type cast for multiple handler not set");
        }

        $type = $this->typeGuess->guessFor($value);

        return isset($type) ? $this->typecast->to($type)->cast($value) : $this->dontCast($value);
    }
}
