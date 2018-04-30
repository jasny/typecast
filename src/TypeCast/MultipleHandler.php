<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler;
use Jasny\TypeCast\BooleanHandler;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\TypeGuess;
use LogicException;

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
     * @param TypeGuess $typeGuess
     */
    public function __construct(TypeGuess $typeGuess = null)
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
        
        if (!$this->shouldCast($value)) {
            return $value;
        }

        $type = $this->typeGuess->guessFor($value);

        return isset($type) ? $this->typecast->forValue($value)->to($type) : $this->dontCast($value);
    }

    /**
     * Check if the value should be casted
     *
     * @param $value
     * @return bool
     */
    protected function shouldCast($value): bool
    {
        if ($value === null || in_array('mixed', $this->types)) {
            return false;
        }

        if (
            is_string($value) &&
            (
                (in_array('boolean', $this->types) && in_array($value, BooleanHandler::getBooleanStrings())) ||
                (is_numeric($value) && array_intersect($this->types, ['integer', 'float']))
            )
        ) {
            return true;
        }

        if (in_array(gettype($value), $this->types)) {
            return false;
        }
    }
}
