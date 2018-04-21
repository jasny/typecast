<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\TypeGuess;

/**
 * Cast value to one of multiple types
 */
class MultipleHandler extends Handler
{
    /**
     * @var TypeGuess
     */
    protected $typeguess;
    
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
     * @param TypeGuess $typeguess
     */
    public function __construct(TypeGuess $typeguess = null)
    {
        $this->typeguess = $typeguess ?? new TypeGuess();
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
     * Check if value is one of the types, otherwise trigger a warning
     * 
     * @param mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        if (!isset($this->typecast)) {
            throw new \LogicException("Type cast for multiple handler not set");
        }
        
        if ($value === null || in_array('mixed', $this->types)) {
            return $value;
        }
        
        return $this->excludeTypes($value)->castIfPossible($value);
    }
    
    /**
     * Cast if there is only a single option
     * 
     * @param mixed $value
     * @return mixed
     */
    protected function castIfPossible($value)
    {
        if (empty($this->types)) {
            return $this->dontCast($value);
        }

        $handler = count($this->types) === 1 ? $this : $this->reduceTypes($value);
        
        if ($handler->matchAnyType($value)) {
            return $value;
        }
        
        if (count($handler->types) === 1) {
            return $this->typecast->forValue($value)->to(reset($handler->types));
        }
        
        return $this->dontCast($value);
    }
    
    /**
     * Get subtypes for typed arrays
     * 
     * @return array
     */
    protected function getSubtypes(): array
    {
        return array_filter(array_map(function($type) {
            return substr($type, -2) === '[]' ? substr($type, 0, -2) : null;
        }, $this->types));        
    }
    
    /**
     * Match the value type against one of the types
     * 
     * @param mixed $value
     * @return bool
     */
    protected function matchAnyType($value): bool
    {
        $valueType = gettype($value);
        
        return array_reduce($this->types, function($found, $type) use ($value, $valueType) {
            return $found || strtolower($type) === $valueType || is_a($value, $type)
                || (is_array($value) && substr($type, -2) === '[]'
                    && $this->forType(substr($type, 0, -2))->allMatchAnyType($value));
        }, false);
    }
    
    /**
     * All items in the array match any of the given types
     * 
     * @param array $value
     * @return bool
     */
    protected function allMatchAnyType(array $value): bool
    {
        return array_reduce($value, function($match, $item) {
            return $match && $this->matchAnyType($item);
        }, true);
    }
    
    /**
     * Eliminate types based on the value and specific combinations
     * 
     * @param mixed $value
     * @return static
     */
    protected function excludeTypes($value): self
    {
        if (count($this->types) === 2 && in_array('null', $this->types)) {
            return $this->forTypes(array_diff($this->types, ['null']));
        }
        
        $types = $this->getPossibleTypes($value);
        
        return $this->forTypes($types);
    }
    
}
