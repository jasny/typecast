<?php

namespace Jasny;

use Jasny\TypeCastInterface;
use Jasny\TypeCast\HandlerInterface;
use Traversable;
use OutOfBoundsException;

// Default handlers
use Jasny\TypeCast\Handler\{
    ArrayHandler,
    BooleanHandler,
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
class TypeCast implements TypeCastInterface
{
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
     * @param iterable|HandlerInterface[] $handlers
     */
    public function __construct(iterable $handlers = null)
    {
        foreach ($handlers ?? static::getDefaultHandlers() as $key => $handler) {
            $this->handlers[$key] = $handler->usingTypecast($this);
        }
    }


    /**
     * Get the handler for a type
     *
     * @param string $type
     * @return HandlerInterface
     * @throws OutOfBoundsException
     */
    public function to(string $type): HandlerInterface
    {
        $type = $this->normalizeType($type);

        if (isset($this->handlers[$type])) {
            $key = $type;
        } elseif ($type === 'integer|float' || $type === 'float|integer') {
            $key = 'number';
        } elseif (strpos($type, '|') !== false) {
            $key = 'multiple';
        } elseif (substr($type, -2) === '[]' || is_a($type, Traversable::class, true)) {
            $key = 'array';
        } else {
            $key = 'object';
        }

        if (!isset($this->handlers[$key])) {
            throw new OutOfBoundsException("Unable to find handler to cast to '$type'");
        }

        return $this->handlers[$key]->forType($type);
    }


    /**
     * Add a custom alias
     *
     * @param string $alias
     * @param string $type
     * @return static
     */
    public function alias(string $alias, string $type): TypeCastInterface
    {
        $copy = clone $this;
        $copy->aliases[$alias] = $type;

        return $copy;
    }

    /**
     * Lowercase internal types and replace alias type with full type.
     *
     * @param string $type
     * @return string
     */
    public function normalizeType(string $type): string
    {
        if (strpos($type, '|') !== false) {
            $types = array_map([$this, __FUNCTION__], explode('|', $type));
            return join('|', $types);
        }

        if (substr($type, -2) === '[]') {
            $subtype = substr($type, 0, -2);
            return $this->normalizeType($subtype) . '[]';
        }

        if (ctype_alpha($type) && !ctype_lower($type) && in_array(strtolower($type), array_keys($this->handlers))) {
            $type = strtolower($type);
        }

        return $this->aliases[$type] ?? $type;
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
