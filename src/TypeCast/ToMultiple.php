<?php

namespace Jasny\TypeCast;

/**
 * Cast value to one of multiple types
 */
trait ToMultiple
{
    /**
     * Trigger a warning that the value can't be casted and return $value
     * 
     * @param array $type
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
     * Check if value is one of the types, otherwise trigger a warning
     * 
     * @param array $types
     * @return mixed
     */
    public function toMultiple($types)
    {
        $types = array_diff($types, ['null']);
        
        if (count($types) === 1) {
            return $this->to(reset($types));
        }
        
        $found = $this->isOneOfType($types);
        
        if (is_string($found)) {
            return $this->to($found);
        } elseif ($found) {
            return $this->value;
        } else {
            return $this->dontCastTo(join('|', $types));
        }
    }
    
    /**
     * Check that all items of value are of a specific type
     * 
     * @param array $types
     */
    protected function allSubValuesAre($types)
    {
        foreach ($this->value as $item) {
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
     * Check if value is one of the type
     * 
     * @param array $types
     * @return boolean|string
     */
    protected function isOneOfType($types)
    {
        $found = false;
        $subtypes = [];
        $valueType = gettype($this->value);
        
        foreach ($types as &$type) {
            $this->normalizeType($type);

            if ($type === $valueType || is_a($this->value, $type)) {
                $found = true;
                break;
            }
            
            if (substr($type, -2) === '[]') {
                $subtypes[] = substr($type, 0, -2);
            }
        }
        
        if (!$found && is_array($this->value) && !empty($subtypes)) {
            $found = $this->allSubValuesAre($subtypes);
        }
        
        if (!$found && count($subtypes) === 1) {
            $found = $subtypes[0] . '[]';
        }
        
        return $found;
    }
}
