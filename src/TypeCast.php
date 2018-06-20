<?php

namespace Jasny;

use Jasny\TypeCastInterface;
use Jasny\TypeCast\HandlerInterface;
use Jasny\TypeCast\HandlerRepositoryInterface;
use Traversable;
use OutOfBoundsException;

// Default handlers
use Jasny\TypeCast\{
    ArrayHandler,
    BooleanHandler,
    FloatHandler,
    IntegerHandler,
    MixedHandler,
    NumberHandler,
    ObjectHandler,
    ResourceHandler,
    StringHandler,
    MultipleHandler
};

/**
 * Class for type casting
 */
class TypeCast implements TypeCastInterface, HandlerRepositoryInterface
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
        'double' => 'float',
        'real' => 'float',
        'number' => 'integer|float',
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
        $this->handlers = $handlers ?? static::getDefaultHandlers();
    }
    
    /**
     * Create a clone of this typecast object for a different value.
     * 
     * @param mixed $value
     * @return static
     */
    public function value($value): TypeCastInterface
    {
        $cast = clone $this;
        $cast->value = $value;
        
        return $cast;
    }

    /**
     * Get the handler for a type
     * 
     * @param string $type
     * @return HandlerInterface
     * @throws OutOfBoundsException
     */
    public function desire(string $type): HandlerInterface
    {
        if (isset($this->handlers[$type])) {
            $key = $type;
        } elseif ($type === 'integer|float' || $type === 'float|integer') {
            $key = 'number';
        } elseif (strstr($type, '|')) {
            $key = 'multiple';
        } elseif (substr($type, -2) === '[]' || is_a($type, Traversable::class, true)) {
            $key = 'array';
        } else {
            $key = 'object';
        }
        
        if (!isset($this->handlers[$key])) {
            throw new OutOfBoundsException("Unable to find handler to cast to '$type'");
        }
        
        return $this->handlers[$key]->forType($type)->usingTypecast($this);
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
     * Get the display name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
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
        if (strstr($type, '|')) {
            $types = array_map([$this, __FUNCTION__], explode('|', $type));
            return join('|', $types);
        }
        
        if (substr($type, -2) === '[]') {
            $subtype = substr($type, 0, -2);
            return $this->normalizeType($subtype) . '[]';
        }
        
        return $this->aliases[$type] ?? $type;
    }
    
    /**
     * Cast value
     *
     * @param string $type
     * @return mixed
     */
    public function to(string $type)
    {
        $normalType = $this->normalizeType($type);

        return $this->getHandler($normalType)->cast($this->value);
    }
    
    
    /**
     * Get the default handlers defined by the Jasny Typecast library
     * 
     * @return HandlerInterface[]
     */
    public static function getDefaultHandlers(): array
    {
        $numberHandler = new NumberHandler();

        return [
            'array' => new ArrayHandler(),
            'boolean' => new BooleanHandler(),
            'float' => $numberHandler,
            'integer' => $numberHandler,
            'number' => $numberHandler,
            'mixed' => new MixedHandler(),
            'object' => new ObjectHandler(),
            'resource' => new ResourceHandler(),
            'string' => new StringHandler(),
            'multiple' => new MultipleHandler()
        ];
    }
}
