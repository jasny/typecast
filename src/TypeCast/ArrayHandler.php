<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler;
use Jasny\TypeCast\HandlerInterface;
use Jasny\TypeCastInterface;

/**
 * Type cast to an array
 */
class ArrayHandler extends Handler
{
    /**
     * @var TypeCastInterface 
     */
    protected $typecast;
    
    /**
     * All values are of this subtype
     * @var string
     */
    protected $subtype;
    
    
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return 'array';
    }
    
    /**
     * Use handler to cast to type.
     * 
     * @param string $type
     * @return static
     * @throws \LogicException if handler can't be used
     */
    public function forType(string $type): HandlerInterface
    {
        if ($type !== 'array' && substr($type, -2) !== '[]') {
            throw new \LogicException("Unable to use " . get_class($this) . " to cast to $type");
        }

        $subtype = $type === 'array' ? null : substr($type, 0, -2);

        if ($subtype === $this->subtype) {
            return $this;
        }
        
        $handler = clone $this;
        $handler->subtype = $subtype;
        
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
        $handler = clone $this;
        $handler->typecast = $typecast;
        
        return $handler;
    }
    
    
    /**
     * Cast value to an array
     *
     * @param mixed $value
     * @return array|mixed
     */
    public function cast($value)
    {
        $fn = 'cast' . ucfirst(gettype($value));
        $array = method_exists($this, $fn) ? $this->$fn($value) : (array)$value;
        
        if (is_array($array) && isset($this->subtype)) {
            foreach ($array as &$item) {
                $item = $this->typecast->forValue($item)->to($this->subtype);
            }
        }
        
        return $array;
    }
    
    
    /**
     * Cast a string to an array
     * 
     * @param mixed $value
     * @return array
     */
    protected function castString($value): array
    {
        return $value === '' ? [] : [$value];
    }
    
    /**
     * Cast a resource to a array
     * 
     * @param mixed $value
     * @return resource
     */
    protected function castResource($value)
    {
        return $this->dontCast($value);
    }
    
    /**
     * Cast an object to a array
     * 
     * @param mixed $value
     * @return array
     */
    protected function castObject($value): array
    {
        if ($value instanceof \Traversable) {
            return iterator_to_array($value);
        }
        
        if ($value instanceof \stdClass) {
            return call_user_func('get_object_vars', $value);
        }
        
        return [$value];
    }
}
