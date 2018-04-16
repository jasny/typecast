<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler;
use Jasny\TypeCast\HandlerInterface;

/**
 * Type cast to an object of a specific class
 */
class ObjectHandler extends Handler
{
    /**
     * Class name
     * @var string
     */
    public $class = null;
    
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return trim("{$this->class} object");
    }
    
    /**
     * Set the class name
     * 
     * @param string $type
     * @return static
     */
    public function forType(string $type): HandlerInterface
    {
        $handler = clone $this;
        $handler->class = $type !== 'object' ? $type : null;
        
        return $handler;
    }
    
    /**
     * Cast value to an object of a class
     *
     * @param mixed $value
     * @return object|mixed
     */
    public function cast($value)
    {
        if ($value === null) {
            return null;
        }
        
        $fn = 'cast' . ucfirst(gettype($value));
        
        if (method_exists($this, $fn)) {
            return $this->$fn($value);
        }
        
        switch (strtolower($this->class)) {
            case '':
                return $this->castToObject($value);
            case 'stdclass':
                return $this->castToStdClass($value);
            default:
                return $this->castToClass($value);
        }
    }
    
    
    /**
     * Cast value to a object
     *
     * @return object|mixed
     */
    protected function castToObject($value)
    {
        if (is_scalar($value)) {
            $value = $this->dontCast($value);
        }
        
        return (object)$value;
    }
    
    /**
     * Cast value to a stdClass object
     * 
     * @return object
     */
    protected function castToStdClass($value)
    {
        if (is_object($value) && !$value instanceof \stdClass) {
            $value = get_object_vars($value);
        }
        
        return $this->castToObject($value);
    }

    /**
     * Cast value to a specific class
     * 
     * @param mixed $value
     * @return object|mixed
     */
    protected function castToClass($value)
    {
        if (!class_exists($this->class)) {
            return $this->dontCast($value, "Class doesn't exist");
        }
        
        if (is_object($value) && is_a($value, $this->class)) {
            return $value;
        }
        
        $class = $this->class;
        
        return method_exists($class, '__set_state')
            ? $this->classSetState($value)
            : new $class($value);
    }
    
    /**
     * Create object using __set_state
     * 
     * @param mixed $value
     * @return object|mixed
     */
    protected function classSetState($value)
    {
        $class = $this->class;
        $method = new \ReflectionMethod($class, '__set_state');
        $param = $method->getParameters()[0];
        
        if ($param->getType() === 'array') {
            if ($value instanceof \stdClass) {
                $value = get_object_vars($value);
            }

            if (!is_array($value)) {
                return $this->dontCast($value, "{$class}::__set_state() expects an array");
            }
        }
        
        return $class::__set_state($value);
    }
}
