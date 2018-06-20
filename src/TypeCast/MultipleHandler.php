<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;
use Jasny\TypeCast\Handler;
use Jasny\TypeCast\HandlerInterface;
use Jasny\TypeCast\TypeGuess;
use Jasny\TypeCast\TypeGuessInterface;
use LogicException;
use Traversable;

/**
 * Cast value to one of multiple types
 */
class MultipleHandler extends Handler
{
    /**
     * @var TypeGuess
     */
    protected $typeGuess;
    
    /**
     * @var TypeCastInterface 
     */
    protected $typecast;
    
    /**
     * Possible types
     * @var array
     */
    protected $types = [];
    
    
    /**
     * Class constructor
     *
     * @param TypeGuessInterface $typeGuess
     */
    public function __construct(TypeGuessInterface $typeGuess = null)
    {
        $this->typeGuess = $typeGuess ?? new TypeGuess();
    }

    /**
     * Use handler to cast to type.
     * 
     * @param string $type
     * @return static
     */
    public function forType(string $type): HandlerInterface
    {
        return $this->forTypes(explode('|', $type));
    }

    /**
     * Use handler to cast to type.
     * 
     * @param string[] $types
     * @return static
     */
    public function forTypes(array $types): HandlerInterface
    {
        $unique = array_unique($types);
        
        if (count($unique) === count($this->types) && count(array_udiff($unique, $this->types, 'strcasecmp')) === 0) {
            return $this;
        }
        
        $handler = clone $this;
        $handler->types = $unique;
        
        return $handler;
    }
    
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return join('|', $this->types);
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
     * Cast the value to one of the types.
     * 
     * @param mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        if (!isset($this->typecast)) {
            throw new LogicException("Type cast for multiple handler not set");
        }

        $cast = $this->shouldCast($value);

        if (!$cast) {
            return $value;
        }

        $type = is_string($cast) ? $cast : $this->typeGuess->guessFor($value);

        return isset($type) ? $this->typecast->forValue($value)->to($type) : $this->dontCast($value);
    }


    /**
     * Check if the value should be casted
     *
     * @param mixed $value
     * @return bool|string
     */
    protected function shouldCast($value)
    {
        if ($value === null || in_array('mixed', $this->types)) {
            return false;
        }

        $type = gettype($value);

        if ($type === 'double') {
            $type = 'float';
        }

        switch ($type) {
            case 'array':
                return $this->shouldCastArray($value, $type);
            case 'object':
                return $value instanceof Traversable ? $this->shouldCastTraversable($value) :
                    $this->shouldCastObject($value);
            default:
                return $this->shouldCastScalar($value, $type);
        }
    }

    /**
     * Check if the scalar value should be casted
     *
     * @param mixedc $value
     * @param string $type
     * @return bool|string
     */
    protected function shouldCastScalar($value, $type)
    {
        if ($type === 'string' && is_numeric($value)) {
            $to = array_intersect(['integer', 'float'], $this->types);

            if (!empty($to)) {
                return count($to) === 1 ? $to[0] : true;
            }
        }

        if (in_array($type, $this->types)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the array value should be casted
     *
     * @param mixed $value
     * @param string $type
     * @return bool|string
     */
    protected function shouldCastArray($value, $type)
    {
        if (
            in_array('array', $this->types) ||
            (empty($value) && strstr(join('|', $this->types), '[]'))
        ) {
            return false;
        }

        $base = reset($value);
        $subtype = gettype($base);

        if ($subtype !== 'object' && !in_array($subtype . '[]', $this->types)) {
            return true;
        }

        if ($subtype === 'object' && !in_array('object[]', $this->types)) {
            $class = get_class($base);
        }

        foreach ($value as $item) {
            if (gettype($item) !== $subtype) {
                return true;
            }

            if (!isset($class) || get_class($item) === $class || is_a($item, $class)) {
                continue;
            }

            $itemClass = get_class($item);
            if (!is_a($class, $itemClass, true)) {
                return true;
            }

            $base = $item;
            $class = $itemClass;
        }

        return isset($class) ? $this->shouldCastArrayWithObjects($base) : false;
    }

    /**
     * Check if the array value should be casted when knowing the class
     *
     * @param mixed $value
     * @return bool|string
     */
    protected function shouldCastArrayWithObjects($value)
    {
        if (array_uintersect([get_class($value) . '[]'], $this->types, 'strcasecmp')) {
            return false;
        }

        $classes = array_diff($this->types, ['string[]', 'boolean[]', 'integer[]', 'float[]', 'array[]', 'object[]',
            'resource[]']);

        foreach ($classes as $class) {
            if (substr($class, -2) === '[]' && is_a($value, substr($class, 0, -2))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the scalar value should be casted
     *
     * @param mixedc $value
     * @return bool|string
     */
    protected function shouldCastObject($value)
    {
        if (array_uintersect([get_class($value)], $this->types, 'strcasecmp')) {
            return false;
        }

        $classes = array_diff($this->types, ['string', 'boolean', 'integer', 'float', 'array', 'object', 'resource']);

        foreach ($classes as $class) {
            if (substr($class, -2) !== '[]' && is_a($value, $class)) {
                return false;
            }
        }

        return true;
    }
}
