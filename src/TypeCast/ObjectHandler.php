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
        $class = $type !== 'object' ? $type : null;
        
        if ($class === $this->class) {
            return $this;
        }
        
        $handler = clone $this;
        $handler->class = $class;
        
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
        if (is_scalar($value) || is_resource($value)) {
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
        
        $value = $this->createWithSetState($value);
        
        if (!is_a($value, $this->class)) {
            $class = $this->class;
            $value = new $class($value);
        }
        
        return $value;
    }
    
    /**
     * Create object using __set_state.
     * @internal Internal objects expect an array but do lot list paramaters, so checking `empty($parmas)`.
     * 
     * @param mixed $value
     * @return object|mixed
     */
    protected function createWithSetState($value)
    {
        if (!method_exists($this->class, '__set_state')) {
            return $value;
        }
        
        $method = new \ReflectionMethod($this->class, '__set_state');
        $params = $method->getParameters();
        
        if (empty($params) || (string)$params[0]->getType() === 'array') {
            if ($value instanceof \stdClass) {
                $value = get_object_vars($value);
            }
            
            if (!is_array($value)) {
                return $value;
            }
        }
        
        return $method->invoke(null, $value);
    }
}
