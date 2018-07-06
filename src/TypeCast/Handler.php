<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;
use LogicException;

/**
 * Base class for type casting handler
 */
abstract class Handler implements HandlerInterface
{
    /**
     * E_* or Throwable class name
     * @var int|string|bool
     */
    protected $failure = E_USER_NOTICE;
    
    /**
     * Variable or property name
     * @var string
     */
    protected $name;
    
    /**
     * Set the warning level or throwable when variable can't be cased to type.
     * Set to `false` to not give any warning or error.
     * 
     * @param int|string|bool $level  E_* or Throwable class name
     */
    public function onFailure($level)
    {
        $this->failure = $level;
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
     * @return static
     * @throws LogicException if handler can't be used
     */
    public function forType(string $type): HandlerInterface
    {
        if ($type !== $this->getType()) {
            throw new LogicException("Unable to use " . get_class($this) . " to cast to $type");
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
     * Get a description of the type of the value
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
        if ($this->failure) {
            $valueType = $this->getValueTypeDescription($value);

            $type = $this->getType();
            $cast = isset($this->name) ? "cast {$this->name} from" : "cast";

            $message = "Unable to {$cast} {$valueType} to $type" . (isset($explain) ? ": $explain" : '');

            if (is_int($this->failure)) {
                trigger_error($message, $this->failure);
            } else {
                $class = $this->failure;
                throw new $class($message);
            }
        }
        
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
