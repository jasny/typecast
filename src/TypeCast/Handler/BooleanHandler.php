<?php

namespace Jasny\TypeCast\Handler;

use Jasny\TypeCast\Handler;

/**
 * Type cast to a boolean
 */
class BooleanHandler extends Handler
{
    /**
     * Get the type to cast to
     * 
     * @return string
     */
    protected function getType(): string
    {
        return 'boolean';
    }
    
    /**
     * Cast value to a boolean
     *
     * @param mixed $value
     * @return boolean|mixed
     */
    public function cast($value)
    {
        $fn = 'cast' . ucfirst(gettype($value));
        
        return method_exists($this, $fn) ? $this->$fn($value) : (boolean)$value;
    }
    
    /**
     * Cast a resource to a boolean
     * 
     * @param mixed $value
     * @return resource
     */
    protected function castResource($value)
    {
        return $this->dontCast($value);
    }
    
    /**
     * Cast an object to a boolean
     * 
     * @param mixed $value
     * @return object
     */
    protected function castObject($value)
    {
        return $this->dontCast($value);
    }
    
    /**
     * Cast an array to a boolean
     * 
     * @param mixed $value
     * @return array
     */
    protected function castArray($value): array
    {
        return $this->dontCast($value);
    }

    /**
     * Cast a string to a boolean
     * 
     * @param mixed $value
     * @return boolean|string
     */
    protected function castString($value)
    {
        $string = strtolower(trim($value));

        if (in_array($string, self::getBooleanStrings(true))) {
            return true;
        }

        if (in_array($string, self::getBooleanStrings(false))) {
            return false;
        }

        return $this->dontCast($value);
    }
    
    /**
     * Get strings that represent true or false
     * 
     * @param bool $state
     * @return array
     */
    public static function getBooleanStrings(bool $state = null): array
    {
        $strings = [
            false => ['', '0', 'false', 'no', 'off'],
            true => ['1', 'true', 'yes', 'on']
        ];
        
        return isset($state) ? $strings[$state] : array_merge($strings[false], $strings[true]);
    }
}
