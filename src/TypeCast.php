<?php

namespace Jasny;

use Jasny\TypeCast;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\HandlerInterface;

/**
 * Class for type casting
 *
 *     $string = TypeCast::value($myValue)->to('string');
 *     $foo = TypeCast::value($data)->to(Foo:class);
 * 
 * When casting to an object of a class, the `__set_state()` method is used if available and the value is an array or a
 * stdClass object.
 */
class TypeCast implements TypeCastInterface
{
    /**
     * @var mixed
     */
    protected $value;
    
    /**
     * The display name
     * @var string
     */
    protected $name;
    
    /**
     * Handlers that do the actual casting
     * @var HandlerInterface[]
     */
    protected $handlers;
    
    /**
     * Type aliases
     * @var string[]
     */
    protected $aliases = [
        'bool' => 'boolean',
        'int' => 'integer',
        'mixed[]' => 'array'
    ];
    
    
    /**
     * Class constructor
     *
     * @param mixed              $value
     * @param HandlerInterface[] $handlers
     */
    public function __construct($value = null, array $handlers = null)
    {
        $this->value = $value;
        $this->handlers = $handlers ?? $this->getDefaultHandlers();
    }
    
    /**
     * Factory method
     *
     * @param mixed $value
     * @return static
     */
    public static function value($value): self
    {
        return new static($value);
    }
    
    /**
     * Create a clone of this typecast object for a different value.
     * 
     * @param mixed $value
     * @return static
     */
    public function forValue($value): TypeCastInterface
    {
        $cast = clone $this;
        $cast->value = $value;
        
        return $cast;
    }

    /**
     * Get the handler for a type
     * 
     * @param string $key
     * @param string $type
     * @return HandlerInterface
     * @throws \OutOfBoundsException
     */
    public function getHandler(string $key, string $type = null): HandlerInterface
    {
        if (!isset($type)) {
            $type = $key;
        }
        
        if (!isset($this->handlers[$key])) {
            throw new \OutOfBoundsException("Unable to find handler to cast to '$type'");
        }
        
        return $this->handlers[$key]->forType($type)->usingTypecast($this)->withName($this->name);
    }
    
    /**
     * Set the display name.
     * This is used in notices.
     * 
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * Add a custom alias
     * 
     * @param string $alias
     * @param string $type
     * @return $this
     */
    public function alias(string $alias, string $type): self
    {
        $this->aliases[$alias] = $type;
        
        return $this;
    }

    /**
     * Replace alias type with full type
     * 
     * @param string $type
     * @return string
     */
    public function normalizeType(string $type): string
    {
        if (substr($type, -2) === '[]') {
            $subtype = substr($type, 0, -2);
            $type = $this->normalizeType($subtype) . '[]';
        }
        
        if (isset($this->aliases[$type])) {
            $type = $this->aliases[$type];
        }
        
        return $type;
    }
    
    /**
     * Cast value
     *
     * @param string $type
     * @return mixed
     */
    public function to(string $type)
    {
        if (strstr($type, '|')) {
            $handler = $this->getHandler('multiple', $type);
        } else {
            $normalType = $this->normalizeType($type);

            if (isset($this->handlers[$normalType])) {
                $handler = $this->getHandler($normalType);
            } else {
                $handler = $this->getHandler(substr($normalType, -2) === '[]' ? 'array' : 'object', $type);
            }
        }
        
        return $handler->cast($this->value);
    }
    
    
    /**
     * Get the default handlers defined by the Jasny Typecast library
     * 
     * @return HandlerInterface[]
     */
    public static function getDefaultHandlers(): array
    {
        return [
            'array' => new TypeCast\ArrayHandler(),
            'boolean' => new TypeCast\BooleanHandler(),
            'float' => new TypeCast\FloatHandler(),
            'integer' => new TypeCast\IntegerHandler(),
            'mixed' => new TypeCast\MixedHandler(),
            'object' => new TypeCast\ObjectHandler(),
            'resource' => new TypeCast\ResourceHandler(),
            'string' => new TypeCast\StringHandler(),
            'multiple' => new TypeCast\MultipleHandler()
        ];
    }
}
