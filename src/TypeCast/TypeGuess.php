<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\BooleanHandler;
use stdClass;
use DateTime;
use Traversable;
use ReflectionClass;

/**
 * Guess a type.
 */
class TypeGuess
{
    /**
     * Possible types
     * @var array
     */
    public $types;


    /**
     * Class constructor
     * 
     * @param string[] $types  Possible types
     */
    public function __construct(array $types = [])
    {
        $this->types = array_values($types);
    }
    
    /**
     * Create a type guess object for these types
     * 
     * @param array $types
     * @return static
     */
    public function forTypes(array $types): self
    {
        if (count($types) === count($this->types) && count(array_diff($types, $this->types)) === 0) {
            return $this;
        }
        
        $typeGuess = clone $this;
        $typeGuess->types = array_values($types);
        
        return $typeGuess;
    }

    /**
     * Get only the subtypes.
     *
     * @return array
     */
    protected function getSubTypes()
    {
        $subTypes = [];

        foreach ($this->types as $type) {
            if (substr($type,-2) === '[]') {
                $subTypes[] = substr($type, 0, -2);
            }
        }

        return $subTypes;
    }


    /**
     * Guess the handler for the value.
     * 
     * @param mixed $value
     * @return string|null
     */
    public function guessFor($value): ?string
    {
        return $this
            ->removeNull()
            ->onlyPossible($value)
            ->reduceScalarTypes($value)
            ->reduceArrayTypes($value)
            ->reduceClasses()
            ->conclude();
    }


    /**
     * Remove the null type
     *
     * @return static
     */
    protected function removeNull(): self
    {
        return $this->forTypes(array_diff($this->types, ['null']));
    }

    /**
     * Return handler with only the possible types for the value.
     *
     * @param mixed $value
     * @return static
     */
    protected function onlyPossible($value): self
    {
        return count($this->types) < 2 ? $this : $this->forTypes($this->getPossibleTypes($value));
    }

    /**
     * Get possible types based on the value
     * 
     * @param mixed $value
     * @return array
     */
    protected function getPossibleTypes($value): array
    {
        if (empty($this->types)) {
            return [];
        }

        $type = $this->isAssoc($value) ? 'assoc' : ($value instanceof Traversable ? 'array' : gettype($value));
        
        switch ($type) {
            case 'boolean':
            case 'integer':
            case 'float':
            case 'string':
                return $this->getPossibleScalarTypes($value);
            case 'array':
                return $this->getPossibleArrayTypes($value);
            case 'assoc':
                return $this->getPossibleAssocTypes($value);
            case 'object':
                return $this->getPossibleObjectTypes($value);
            case 'resource':
            default:
                return array_intersect($this->types, [$type]);
        }
    }
    
    /**
     * Get possible types based on a scalar value
     * 
     * @param mixed $value
     * @return array
     */
    protected function getPossibleScalarTypes($value): array
    {
        $singleTypes = array_filter($this->types, function($type) {
            return substr($type, -2) !== '[]';
        });
        
        $not = [
            'string' => is_bool($value),
            'integer' => is_string($value) && !is_numeric($value),
            'float' => is_bool($value) || (is_string($value) && !is_numeric($value)),
            'boolean' => is_string($value) && !in_array($value, BooleanHandler::getBooleanStrings()),
            'array' => true,
            'object' => true,
            'resource' => true,
            'stdClass' => true,
            'DateTime' => is_bool($value) || is_float($value) || (is_string($value) && strtotime($value) === false)
        ];

        return array_udiff($singleTypes, array_keys(array_filter($not)), 'strcasecmp');
    }

    /**
     * Get possible types based on a (numeric) array value
     *
     * @param iterable $value
     * @return array
     */
    protected function getPossibleArrayTypes(iterable $value): array
    {
        $noSubTypes = in_array('array', $this->types);

        $types = array_filter($this->types, function($type) use($noSubTypes) {
            return $type === 'array'
                || (!$noSubTypes && substr($type, -2) === '[]')
                || (class_exists($type) && is_a(Traversable::class, $type, true));
        });

        return $noSubTypes ? $types : $this->removeImpossibleArraySubtypes($types, $value);
    }

    /**
     * Remove subtypes that aren't available for each item.
     *
     * @param array    $types
     * @param iterable $value
     * @return array
     */
    protected function removeImpossibleArraySubtypes(array $types, iterable $value): array
    {
        $subTypes = $this->getSubTypes();

        if (count($subTypes) === 0) {
            return $types;
        }

        $subHandler = $this->forTypes($subTypes);

        foreach ($value as $item) {
            $subHandler = $subHandler->forTypes($subHandler->getPossibleTypes($item));
        }

        $possibleSubTypes = $subHandler->types;

        return count($possibleSubTypes) === count($subTypes)
            ? $types
            : array_filter($types, function($type) use ($possibleSubTypes) {
                return substr($type, -2) !== '[]' || in_array(substr($type, 0, -2), $possibleSubTypes);
            });
    }
    
    /**
     * Get possible types based on associated array or stdClass object.
     *
     * @param mixed $value
     * @return array
     */
    protected function getPossibleAssocTypes($value): array
    {
        $exclude = ['string', 'integer', 'float', 'boolean', 'resource', 'DateTime'];
        
        return array_udiff($this->types, $exclude, 'strcasecmp');
    }

    /**
     * Get possible types based on an object.
     * 
     * @param object $value
     * @return array
     */
    protected function getPossibleObjectTypes($value): array
    {
        return array_filter($this->types, function($type) use ($value) {
            return ((class_exists($type) || interface_exists($type)) && is_a($value, $type))
                || ($type === 'string' && method_exists($value, '__toString'))
                || (in_array($type, ['object', 'array']) && $type instanceof stdClass);
        });
    }


    /**
     * Remove scalar types that are unlikely to be preferred.
     *
     * @param mixed $value
     * @return static
     */
    protected function reduceScalarTypes($value): self
    {
        if (!is_scalar($value) || count($this->types) === 1) {
            return $this;
        }

        $preferredTypes = ['string', 'integer', 'float', 'boolean', DateTime::class];
        $types = array_uintersect($this->types, $preferredTypes, 'strcasecmp');

        if (empty($types)) {
            return $this;
        }

        $remove = [];

        if (in_array(DateTime::class, $types) || in_array('boolean', $types)) {
            $remove[] = 'string';
        }

        if (in_array('integer', $types)) {
            $remove[] = DateTime::class;
        }

        if (in_array('boolean', $types) && is_bool($value)) {
            $remove[] = 'integer';
            $remove[] = 'float';
        } elseif (in_array('integer', $types) || in_array('float', $types)) {
            $remove[] = 'boolean';
            $remove[] = 'string';
        }

        if (in_array('integer', $types) && in_array('float', $types)) {
            $remove[] = is_float($value) || (is_string($value) && strstr($value, '.')) ? 'integer' : 'float';
        }

        return $this->forTypes(array_udiff($types, $remove, 'strcasecmp'));
    }

    /**
     * Remove scalar types that are unlikely to be preferred.
     *
     * @param mixed $value
     * @return static
     */
    protected function reduceArrayTypes($value): self
    {
        if (!is_iterable($value) || count($this->types) === 1 || in_array('array', $this->types)) {
            return $this;
        }

        $types = $this->types;
        $remove = [];

        if (in_array('DateTime[]', $types) || in_array('boolean[]', $types)) {
            $remove[] = 'string[]';
        }

        if (in_array('integer[]', $types)) {
            $remove[] = 'DateTime[]';
        }

        if (in_array('integer[]', $types) || in_array('float[]', $types)) {
            $remove[] = 'boolean[]';
            $remove[] = 'string[]';
        }

        if (in_array('integer[]', $types) && in_array('float[]', $types)) {
            $float = false;

            foreach ($value as $item) {
                $float = $float || is_float($value) || (is_string($value) && strstr($value, '.'));
            }

            $remove[] = $float ? 'integer' : 'float';
        }

        return $this->forTypes(array_udiff($types, $remove, 'strcasecmp'));
    }

    /**
     * Pick concrete classes over abstract classes and interfaces, plus remove classes that are super seeded.
     * 
     * @return static
     */
    protected function reduceClasses(): self
    {
        if (count($this->types) === 1) {
            return $this;
        }

        $remove = [];

        $classes = array_filter($this->types, function($type) {
            return class_exists($type) || interface_exists($type);
        });

        if (count($classes) < 2) {
            return $this;
        }

        $reflections = array_map(function($class) {
            return new ReflectionClass($class);
        });

        foreach ($reflections as $class) {
            foreach ($reflections as $compare) {
                if ($class->isSubclassOf($compare)) {
                    $classIsAbstract = $class->isAbstract() || $class->isInterface();
                    $compareIsAbstract = $compare->isAbstract() || $compare->isInterface();

                    $remove[] = $compareIsAbstract && !$classIsAbstract ? $compare->name : $class->name;
                    break;
                }
            }
        }

        return $this->forTypes(array_diff($this->types, $remove));
    }

    /**
     * Get the type if there is only one option left.
     *
     * @return string|null
     */
    protected function conclude(): ?string
    {
        if (count($this->types) < 2) {
            return reset($this->types) ?: null;
        }

        if (
            count($this->types) === 2 &&
            (is_a($this->types[0], Traversable::class, true) xor is_a($this->types[1], Traversable::class, true))
        ) {
            $types = is_a($this->types[0], Traversable::class, true) ? $this->types : array_reverse($this->types);
            return sprintf('%s|%s[]', ...$types);
        }

        return null;
    }


    /**
     * Check if value is an associated array or stdClass object
     * 
     * @param mixed $value
     * @return bool
     */
    protected function isAssoc($value): bool
    {
        return (is_array($value) && count(array_filter(array_keys($value), 'is_string')) > 0)
            || (is_object($value) && get_class($value) === stdClass::class);
    }
}
