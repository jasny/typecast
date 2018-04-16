<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;

/**
 * Cast value to one of multiple types
 */
trait ToMultiple
{
    /**
     * Get the value
     * 
     * @return mixed
     */
    abstract public function getValue();
    
    /**
     * Create a clone of this typecast object for a different value
     * 
     * @param mixed $value
     * @return TypeCastInterface|static
     */
    abstract public function forValue($value): TypeCastInterface;
    
    /**
     * Trigger a warning that the value can't be casted and return $value
     * 
     * @param string $type
     * @param array  $explain  Additional message
     * @return mixed
     */
    abstract public function dontCastTo(string $type, string $explain = null);
    
    /**
     * Cast value
     *
     * @param string $type
     * @return mixed
     */
    abstract public function to(string $type);

    /**
     * Replace alias type with full type
     * 
     * @param string $type
     * @return string
     */
    abstract public function normalizeType(string $type): string;
    
    /**
     * Get strings that represent true or false
     * 
     * @param boolean|null $state
     * @return array
     */
    abstract protected function getBooleanStrings($state = null): array;
    
    /**
     * Get the internal types
     * 
     * @return array
     */
    abstract protected function getInternalTypes(): array;
    
    
    /**
     * Check if value is one of the types, otherwise trigger a warning
     * 
     * @param array $types
     * @return mixed
     */
    public function toMultiple(array $types)
    {
        if ($this->getValue() === null) {
            return null;
        }
        
        $normalTypes = array_map([$this, 'normalizeType'], array_diff($types, ['null']));
        
        if ($this->isOneOfType($normalTypes)) {
            $value = $this->getValue();
        } elseif (count($normalTypes) === 1) {
            $value = $this->to(reset($normalTypes));
        } else {
            $value = $this->guessToMultiple($normalTypes);
        }
        
        return $value;
    }
    
    /**
     * Get the subtypes 
     * 
     * @param array $types
     * @return array
     */
    protected function getSubtypes(array $types): array
    {
        $subtypes = [];
        
        foreach ($types as $type) {
            if (substr($type, -2) === '[]') {
                $subtypes[] = substr($type, 0, -2);
            }
        }
        
        return $subtypes;
    }
    
    /**
     * Check that all items of value are of a specific type
     * 
     * @param array $types
     * @return bool
     */
    protected function allSubValuesAre(array $types): bool
    {
        foreach ($this->getValue() as $item) {
            $compare = function ($type) use ($item) {
                return gettype($item) === $type || is_a($item, $type);
            };
                
            if (max(array_map($compare, $types)) !== true) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Match the value type against one of the types
     * 
     * @param array $types
     * @return bool
     */
    public function isOneOfType(array $types): bool
    {
        $valueType = gettype($this->getValue());
        
        $found = array_reduce($types, function ($found, $type) use ($valueType) {
            return $found || $type === 'mixed' || $type === $valueType || is_a($this->getValue(), $type);
        }, false);
        
        if (!$found && is_array($this->getValue())) {
            $subtypes = $this->getSubtypes($types);
            $found = !empty($subtypes) && $this->allSubValuesAre($subtypes);
        }
        
        return $found;
    }
    
    /**
     * Guess the typecasting for multiple types
     * 
     * @param array  $types
     * @param string $asked  The asked types
     * @return mixed
     */
    protected function guessToMultiple(array $types, string $asked = null)
    {
        if (!isset($asked)) {
            $asked = join('|', $types);
        }
        
        $possibleTypes = $this->eliminateTypesForMultipe($types);

        if (empty($possibleTypes)) {
            $value = $this->dontCastTo($asked);
        } elseif (count($possibleTypes) === 1) {
            $value = $this->to(reset($possibleTypes));
        } else {
            $value = $this->guessToMultipleArray($possibleTypes, $asked);
        }
        
        return $value;
    }
    
    /**
     * Guess the typecasting for multiple types which all are a typed array
     * 
     * @param array  $types
     * @param string $asked  The asked types
     * @return mixed
     */
    protected function guessToMultipleArray(array $types, string $asked)
    {
        $subtypes = $this->getSubtypes($types);

        if ($this->multipleIsATypeSubtypeCombination($types, $subtypes)) {
            $value = $this->to($subtypes[0]);
        } elseif (count($subtypes) !== count($types)) {
            $value = $this->dontCastTo($asked);
        } elseif ($this->isOneOfType($subtypes)) {
            $value = [$this->getValue()];
        } else {
            $value = $this->to('array');
            if (is_array($this->getValue()) || $this->getValue() instanceof \stdClass) {
                $asked = null;
            }

            foreach ($value as &$item) {
                $cast = $this->forValue($item);
                if (!$cast->isOneOfType($subtypes)) {
                    $item = $cast->guessToMultiple($subtypes, $asked);
                }
            }
        }
        
        return $value;
    }
    
    /**
     * Check if multiple is a type and subtype combi (eg int|int[]) and value is not an array.
     * 
     * @internal The case where the value IS an array and it's an internal type is already eliminated.
     * 
     * @param array $types
     * @param array $subtypes
     * @return bool
     */
    protected function multipleIsATypeSubtypeCombination(array $types, array $subtypes): bool
    {
        return count($types) === 2 && count($subtypes) === 1 && in_array($subtypes[0], $types)
            && !is_array($this->getValue()) && !$this->getValue() instanceof \stdClass;
    }
    
    /**
     * Remove the types to which the value can't be cast
     * 
     * @param array $types
     * @return array
     */
    protected function eliminateTypesForMultipe(array $types): array
    {
        $exclude = $this->excludeTypeForMultiple();
        
        return array_diff($types, $exclude);
    }

    /**
     * Eliminate types based on wether or not the value is a scalar
     * 
     * @return array
     */
    protected function excludeTypeForMultiple(): array
    {
        $value = $this->getValue();
        
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

        if (is_string($value) && !in_array($value, $this->getBooleanStrings())) {
            $exclude[] = 'boolean';
        }
        
        return $exclude;
    }
}
