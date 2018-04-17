<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;

/**
 * Base class for type casting handler
 */
abstract class Handler implements HandlerInterface
{
    /**
     * Variable or property name
     * @var string
     */
    protected $name;
    
    /**
     * Set the display name.
     * This is used in notices.
     * 
     * @param string|null $name
     * @return static
     */
    public function withName($name): HandlerInterface
    {
        if ($this->name === $name) {
            return $this;
        }
        
        $handler = clone $this;
        $handler->name = $name;
        
        return $handler;
    }
    
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
            $valueType = get_resource_type($value) . " resource";
        } elseif (is_object($value)) {
            $valueType = get_class($value) . " object";
        } elseif (is_string($value)) {
            $valueType = "string \"{$value}\"";
        } else {
            $valueType = gettype($value);
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
        $cast = isset($this->name) ? "cast {$this->name} from" : "cast";
        
        $message = "Unable to {$cast} {$valueType} to $type" . (isset($explain) ? ": $explain" : '');
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
