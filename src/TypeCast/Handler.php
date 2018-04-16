<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;

/**
 * Base class for type casting handler
 */
abstract class Handler implements HandlerInterface
{
    /**
     * Set typecast
     * 
     * @param TypeCastInterface $typecast
     * @return static
     */
    public function usingTypecast(TypeCastInterface $typecast): HandlerInterface
    {
        return $this;
    }
    
    /**
     * Use handler to cast to type.
     * 
     * @param string $type
     * @return HandlerInterface
     * @throws \LogicException if handler can't be used
     */
    public function forType(string $type): HandlerInterface
    {
        if ($type !== $this->getType()) {
            throw new \LogicException("Unable to use " . get_class($this) . " to cast to $type");
        }
        
        return $this;
    }
    
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    abstract protected function getType(): string;

    /**
     * Get a descript of the type of the value
     *
     * @param mixed $value
     * @return string
     */
    protected function getValueTypeDescription($value): string
    {
        if (is_resource($value)) {
            $valueType = "a " . get_resource_type($value) . " resource";
        } elseif (is_array($value)) {
            $valueType = "an array";
        } elseif (is_object($value)) {
            $valueType = "a " . get_class($value) . " object";
        } elseif (is_string($value)) {
            $valueType = "string \"{$value}\"";
        } else {
            $valueType = "a " . gettype($value);
        }

        return $valueType;
    }
    
    /**
     * Trigger a warning that the value can't be casted and return $value
     * 
     * @param mixed $value
     * @param string $explain  Additional message
     * @return mixed
     */
    protected function dontCast($value, string $explain = null)
    {
        $valueType = $this->getValueTypeDescription($value);
        
        $type = $this->getType();
        $name = isset($this->name) ? " {$this->name} from" : '';
        
        $message = "Unable to cast {$name} {$valueType} to $type" . (isset($explain) ? ": $explain" : '');
        trigger_error($message, E_USER_NOTICE);
        
        return $value;
    }
    
    /**
     * Cast null
     * 
     * @return null
     */
    protected function castNull()
    {
        return null;
    }
}
