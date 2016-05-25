<?php

namespace Jasny\TypeCast;

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
     * Trigger a warning that the value can't be casted and return $value
     * 
     * @param string $type
     * @param array $explain  Additional message
     * @return mixed
     */
    abstract public function dontCastTo($type, $explain = null);
    
    /**
     * Cast value
     *
     * @param string $type
     * @return mixed
     */
    abstract public function to($type);

    /**
     * Replace alias type with full type
     * 
     * @param string $type
     * @return void
     */
    abstract public function normalizeType(&$type);
    
    /**
     * Get strings that represent true or false
     * 
     * @param boolean|null $state
     * @return array
     */
    abstract protected function getBooleanStrings($state = null);
    
    /**
     * Get the internal types
     * 
     * @return array
     */
    abstract protected function getInternalTypes();
    
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
        
        $types = array_diff($types, ['null']);
        array_walk($types, [$this, 'normalizeType']);
        
        if ($this->isOneOfType($types)) {
            $value = $this->getValue();
        } elseif (count($types) === 1) {
            $value = $this->to(reset($types));
        } else {
            $value = $this->guessToMultiple($types);
        }
        
        return $value;
    }
    
    /**
     * Get the subtypes 
     * 
     * @param array $types
     * @return array
     */
    protected function getSubtypes($types)
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
     * @return boolean
     */
    protected function allSubValuesAre($types)
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
     * @return boolean
     */
    public function isOneOfType(array $types)
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
    protected function guessToMultiple(array $types, $asked = null)
    {
        if (!isset($asked)) {
            $asked = join('|', $types);
        }
        
        $this->eliminateTypesForMultipe($types);

        if (empty($types)) {
            $value = $this->dontCastTo($asked);
        } elseif (count($types) === 1) {
            $value = $this->to(reset($types));
        } else {
            $value = $this->guessToMultipleArray($types, $asked);
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
    protected function guessToMultipleArray(array $types, $asked)
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
     * @return boolean
     */
    protected function multipleIsATypeSubtypeCombination($types, $subtypes)
    {
        return count($types) === 2 && count($subtypes) === 1 && in_array($subtypes[0], $types)
            && !is_array($this->getValue()) && !$this->getValue() instanceof \stdClass;
    }
    
    /**
     * Remove the types to which the value can't be cast
     * 
     * @param array $types
     */
    protected function eliminateTypesForMultipe(array &$types)
    {
        $types = array_diff($types, ['resource']);
        
        $this->eliminateTypesForMultipleScalar($types);
        $this->eliminateTypesForMultipleString($types);
        
        return $types;
    }

    /**
     * Eliminate types based on wether or not the value is a scalar
     * 
     * @param array $types
     */
    protected function eliminateTypesForMultipleScalar(array &$types)
    {
        $value = $this->getValue();
        
        if (is_scalar($value)) {
            $types = array_diff($types, ['stdClass']);
        } else {
            $types = array_diff($types, ['integer', 'float', 'boolean']);
        
            if (!is_object($value) || !method_exists($value, '__toString')) {
                $types = array_diff($types, ['string']);
            }
        }
    }
    
    /**
     * Eliminate when value is a string
     * 
     * @param array $types
     */
    protected function eliminateTypesForMultipleString(array &$types)
    {
        $value = $this->getValue();
        
        if (is_string($value)) {
            if (!is_numeric($value)) {        
                $types = array_diff($types, ['integer', 'float']);
            }

            if (!in_array($value, $this->getBooleanStrings())) {
                $types = array_diff($types, ['boolean']);
            }
        }
    }
}
