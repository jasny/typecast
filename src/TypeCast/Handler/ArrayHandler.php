<?php

namespace Jasny\TypeCast\Handler;

use Jasny\TypeCast\Handler;
use Jasny\TypeCast\HandlerInterface;
use Jasny\TypeCast\HandlerRepositoryInterface;
use Jasny\TypeCastInterface;
use stdClass;
use Traversable;
use LogicException;

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
     * Use Traversable rather than regular array.
     * Assumes that Traversable takes array as first argument of constructor.
     *
     * @var string|null  class name
     */
    protected $traversable;

    /**
     * All values are of this subtype
     * @var string|null  type or class name
     */
    protected $subtype;
    
    
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return join('|', array_filter([
            $this->traversable,
            isset($this->subtype) ? $this->subtype . '[]' : null
        ])) ?: 'array';
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
        $traversable = null;
        $subtype = null;

        if (strstr($type, '|') && substr($type, -2) === '[]') {
            list($traversable, $type) = explode('|', $type, 2);
            $subtype = substr($type, 0, -2);
        } elseif (substr($type, -2) === '[]') {
            $subtype = substr($type, 0, -2);
        } elseif (is_a($type, Traversable::class, true)) {
            $traversable = $type;
        } elseif ($type !== 'array') {
            throw new LogicException("Unable to use " . get_class($this) . " to cast to $type");
        }

        if ($traversable === $this->traversable && $subtype === $this->subtype) {
            return $this;
        }
        
        $handler = clone $this;
        $handler->traversable = $traversable;
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
        if ($this->typecast === $typecast) {
            return $this;
        }

        $handler = clone $this;
        $handler->typecast = $typecast;
        $handler->name = $typecast->getName();
        
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
        if (isset($this->traversable) && is_a($this->traversable, Traversable::class, true)) {
            return $this->dontCast($value, "{$this->traversable} is not Traversable");
        }

        $fn = 'cast' . ucfirst(gettype($value));
        $result = method_exists($this, $fn) ? $this->$fn($value) : (array)$value;

        if (!is_iterable($result)) {
            return $result;
        }
        
        if (isset($this->subtype)) {
            $result = $this->typecast instanceof HandlerRepositoryInterface
                ? $this->castEachWithHandler($result, $this->typecast)
                : $this->castEach($result);
        }

        if (is_array($result) && isset($this->traversable)) {
            $class = $this->traversable;
            $result = new $class($result);
        }
        
        return $result;
    }

    /**
     * Cast each value of the array.
     *
     * @param iterable $array
     * @return iterable
     * @throws LogicException if typecast is not set
     */
    protected function castEach(iterable $array)
    {
        if (!isset($this->typecast)) {
            throw new LogicException("Typecast for array handler not set");
        }

        foreach ($array as &$item) {
            $item = $this->typecast->forValue($item)->to($this->subtype);
        }

        return $array;
    }

    /**
     * Cast each value of the array using a handler.
     * This is faster than creating a new typecast object for each item.
     *
     * @param iterable                   $array
     * @param HandlerRepositoryInterface $repo
     * @return iterable
     */
    protected function castEachWithHandler(iterable $array, HandlerRepositoryInterface $repo)
    {
        $handler = $repo->getHandler($this->subtype);

        foreach ($array as &$item) {
            $item = $handler->cast($item);
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
        return $this->subtype === 'resource' ? [$value] : $this->dontCast($value);
    }
    
    /**
     * Cast an object to a array
     * 
     * @param mixed $value
     * @return array
     */
    protected function castObject($value): array
    {
        if ($value instanceof Traversable && (!isset($this->traversable) || !is_a($value, $this->traversable, true))) {
            return iterator_to_array($value);
        }
        
        if ($value instanceof stdClass) {
            return call_user_func('get_object_vars', $value);
        }
        
        return [$value];
    }
}
