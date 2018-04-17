<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\BooleanHandler;

/**
 * Cast value to one of multiple types
 */
class MultipleHandler extends Handler
{
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
     * Use handler to cast to type.
     * 
     * @param string $type
     * @return static
     */
    public function forType(string $type): HandlerInterface
    {
        $types = array_unique(explode('|', $type));
        
        if (count($types) === count($this->types) && count(array_diff($types, $this->types)) === 0) {
            return $this;
        }
        
        $handler = clone $this;
        $handler->types = $types;
        
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
        
        if ($value === null) {
            return null;
        }
        
        $normalTypes = array_map([$this->typecast, 'normalizeType'], array_diff($this->types, ['null']));
        
        return $this->forTypes($normalTypes)->castNormalized($value);
    }
    
    /**
     * Cast to normalized type
     * 
     * @param mixed $value
     * @return type
     */
    protected function castNormalized($value)
    {
        if ($this->isOneOfType()) {
            return $value;
        }
        
        if (count($this->types) === 1) {
            return $this->typecast->forValue($value)->to(reset($this->types));
        }
        
        $possibleTypes = array_diff($this->types, $this->excludeTypeForMultiple());
        
        return $this->forTypes($possibleTypes)->guessToMultiple($value);
    }
    
    /**
     * Get the subtypes 
     * 
     * @param array $types
     * @return array
     */
    protected function getSubtypes(): array
    {
        $subtypes = [];
        
        foreach ($this->types as $type) {
            if (substr($type, -2) === '[]') {
                $subtypes[] = substr($type, 0, -2);
            }
        }
        
        return $subtypes;
    }
    
    /**
     * Check that all items of value are of a specific type
     * 
     * @param mixed $value
     * @return bool
     */
    protected function allValuesAre($value): bool
    {
        foreach ($value as $item) {
            $compare = function ($type) use ($item) {
                return gettype($item) === $type || is_a($item, $type);
            };
                
            if (max(array_map($compare, $this->types)) !== true) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Match the value type against one of the types
     * 
     * @param mixed $value
     * @return bool
     */
    public function isOneOfType($value): bool
    {
        $valueType = gettype($value);
        
        $found = array_reduce($this->types, function ($found, $type) use ($value, $valueType) {
            return $found || $type === 'mixed' || $type === $valueType || is_a($value, $type);
        }, false);
        
        if (!$found && is_array($value)) {
            $subtypes = $this->getSubtypes();
            $found = !empty($subtypes) && $this->forTypes($subtypes)->allValuesAre($value);
        }
        
        return $found;
    }
    
    /**
     * Guess the typecasting for multiple types
     * 
     * @param mixed $value
     * @return mixed
     */
    protected function guessToMultiple($value)
    {
        if (empty($this->types)) {
            return $this->dontCast($value);
        }
        
        if (count($this->types) === 1) {
            return $this->typecast->forValue($value)->to(reset($this->types));
        }
        
        return $this->forTypes($this->types)->guessToMultipleArray($value);
    }
    
    /**
     * Guess the typecasting for multiple types which all are a typed array
     * 
     * @param mixed $value
     * @return mixed
     */
    protected function guessToMultipleArray($value)
    {
        $subtypes = $this->getSubtypes();

        if ($this->multipleIsATypeSubtypeCombination($value, $subtypes)) {
            return $this->typecast->forValue($value)->to($subtypes[0]);
        }
        
        if (count($subtypes) !== count($this->types)) {
            return $this->dontCast($value);
        }
        
        if ($this->forTypes($subtypes)->isOneOfType($value)) {
            $value = [$value];
        }
        
        if (is_object($value) && $value instanceof \stdClass) {
            $value = get_object_vars($value);
        } elseif (!is_array($value)) {
            $value = [$value];
        }
        
        $subcast = $this->forTypes($subtypes);

        foreach ($value as &$item) {
            $item = $subcast->cast($item);
        }
        
        return $value;
    }
    
    /**
     * Check if multiple is a type and subtype combi (eg int|int[]) and value is not an array.
     * 
     * @internal The case where the value IS an array and it's an internal type is already eliminated.
     * 
     * @param mixed $value
     * @param array $subtypes
     * @return bool
     */
    protected function multipleIsATypeSubtypeCombination($value, array $subtypes): bool
    {
        return count($this->types) === 2 && count($subtypes) === 1 && in_array($subtypes[0], $this->types)
            && !is_array($value) && !$value instanceof \stdClass;
    }
    
    /**
     * Eliminate types based on wether or not the value is a scalar
     * 
     * @return array
     */
    protected function excludeTypeForMultiple(): array
    {
        $value = $value;
        
        if (is_scalar($value)) {
            $exclude = ['resource', 'stdClass'];
        } else {
            $exclude = ['resource', 'integer', 'float', 'boolean'];
        
            if (!is_object($value) || !method_exists($value, '__toString')) {
                $exclude[] = 'string';
            }
        }
        
        if (is_string($value) && !is_numeric($value)) {        
            $exclude = array_merge($exclude, ['integer', 'float']);
        }

        if (is_string($value) && !in_array($value, BooleanHandler::getBooleanStrings())) {
            $exclude[] = 'boolean';
        }
        
        return $exclude;
    }
}
