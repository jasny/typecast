<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\Handler\BooleanHandler;
use stdClass;
use DateTime;
use Traversable;

/**
 * Guess a type.
 *
 * @internal Concessions to code quality have been made in order to increase performance a bit.
 */
class TypeGuess implements TypeGuessInterface
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * Possible types
     * @var array
     */
    public $types = [];


    /**
     * Create a type guess object for these types
     *
     * @param array $types
     * @return static
     */
    protected function setTypes(array $types): TypeGuessInterface
    {
        $this->types = $types;

        return $this;
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
     * @param array $types
     * @return string|null
     */
    public function guess($value, array $types): ?string
    {
        $this->value = $value;
        $this->setTypes($types);

        return $this
            ->removeNull()
            ->onlyPossible()
            ->reduceScalarTypes()
            ->reduceArrayTypes()
            ->pickArrayForScalar()
            ->conclude();
    }


    /**
     * Remove the null type
     *
     * @return static
     */
    protected function removeNull(): self
    {
        return $this->setTypes(array_diff($this->types, ['null']));
    }

    /**
     * Return handler with only the possible types for the value.
     *
     * @return static
     */
    protected function onlyPossible(): self
    {
        if (count($this->types) < 2) {
            return $this;
        }

        $possible = $this->getPossibleTypes($this->value);

        return empty($possible) ? $this : $this->setTypes($possible);
    }

    /**
     * Get possible types based on the value
     *
     * @param mixed $value
     * @return array
     */
    protected function getPossibleTypes($value): array
    {
        $type = $this->getTypeOf($value);

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
        return array_filter($this->types, function(string $type) use ($value) {
            if (substr($type, -2) === '[]') {
                return false;
            }

            switch (strtolower($type)) {
                case 'string':
                    return !is_bool($value);
                case 'integer':
                    return !is_string($value) || is_numeric($value);
                case 'float':
                    return !is_bool($value) && (!is_string($value) || is_numeric($value));
                case 'boolean':
                    return !is_string($value) || in_array($value, BooleanHandler::getBooleanStrings());
                case 'datetime':
                    return !is_bool($value) && !is_float($value) && (!is_string($value) || strtotime($value) !== false);
                case 'array':
                case 'object':
                case 'resource':
                case 'stdclass':
                    return false;
                default:
                    return true;
            }
        });
    }

    /**
     * Get possible types based on a (numeric) array value
     *
     * @param array $value
     * @return array
     */
    protected function getPossibleArrayTypes(array $value): array
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
     * @return array
     */
    protected function removeImpossibleArraySubtypes(array $types): array
    {
        $subTypes = $this->getSubTypes();

        if (count($subTypes) === 0) {
            return $types;
        }

        $subHandler = $this->setTypes($subTypes);

        foreach ($this->value as $item) {
            $subHandler = $subHandler->setTypes($subHandler->getPossibleTypes($item));
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
     * @param array $value [<description>]
     * @return array
     */
    protected function getPossibleAssocTypes(array $value): array
    {
        $exclude = ['string', 'integer', 'float', 'boolean', 'resource', 'DateTime'];

        return array_udiff($this->types, $exclude, 'strcasecmp');
    }

    /**
     * Get possible types based on an object.
     *
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
     * @return static
     */
    protected function reduceScalarTypes(): self
    {
        if (count($this->types) < 2 || !is_scalar($this->value)) {
            return $this;
        }

        $preferredTypes = ['string', 'integer', 'float', 'boolean', DateTime::class];
        $types = array_uintersect($this->types, $preferredTypes, 'strcasecmp');

        if (count($types) < 2) {
            return empty($types) ? $this : $this->setTypes($types);
        }

        $remove = [];

        if (count(array_uintersect($types, [DateTime::class], 'strcasecmp')) > 0) {
            $remove[] = 'string';
        }

        if (in_array('integer', $types)) {
            $remove[] = DateTime::class;
        }

        if (in_array('boolean', $types) && is_bool($this->value)) {
            $remove[] = 'integer';
            $remove[] = 'float';
            $remove[] = 'string';
        } elseif (in_array('integer', $types) || in_array('float', $types)) {
            $remove[] = 'boolean';
            $remove[] = 'string';
        } elseif (in_array('boolean', $types) && in_array('string', $types)) {
            $remove[] = 'boolean';
        }

        if (in_array('integer', $types) && in_array('float', $types)) {
            $remove[] = is_float($this->value) || (is_string($this->value) && strpos($this->value, '.') !== false) ? 'integer' : 'float';
        }

        return $this->setTypes(array_udiff($types, $remove, 'strcasecmp'));
    }

    /**
     * Remove scalar types that are unlikely to be preferred.
     *
     * @return static
     */
    protected function reduceArrayTypes(): self
    {
        if (count($this->types) === 1 || !is_iterable($this->value) || in_array('array', $this->types)) {
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

            foreach ($this->value as $item) {
                $float = $float || is_float($this->value) || (is_string($this->value) && strpos($this->value, '.') !== false);
            }

            $remove[] = $float ? 'integer[]' : 'float[]';
        }

        return $this->setTypes(array_udiff($types, $remove, 'strcasecmp'));
    }

    /**
     * If all types are arrays and the value is not, guess an array type.
     *
     * @return TypeGuess
     */
    protected function pickArrayForScalar(): self
    {
        if (
            count($this->types) < 2 ||
            (!is_scalar($this->value) && (!is_object($this->value) || get_class($this->value) === stdClass::class))
        ) {
            return $this;
        }

        if (in_array('array', $this->types)) {
            $types = array_filter($this->types, function($type) {
                return substr($type, -2) !== '[]';
            });

            return $this->setTypes($types);
        }

        $subtypes = $this->getSubTypes();
        if (count($subtypes) !== count($this->types)) {
            return $this;
        }

        $type = $this->guess($this->value, $subtypes);

        return $type ? $this->setTypes([$type . '[]']) : $this;
    }

    /**
     * Get the type if there is only one option left.
     *
     * @return string|null
     */
    protected function conclude(): ?string
    {
        $types = array_values($this->types);

        // Reset
        $this->types = [];
        $this->value = null;

        if (count($types) < 2) {
            return reset($types) ?: null;
        }

        if (
            count($types) === 2 &&
            (is_a($types[0], \Traversable::class, true) xor is_a($types[1], \Traversable::class, true))
        ) {
            $args = is_a($types[0], \Traversable::class, true) ? $types : array_reverse($types);
            return vsprintf('%s|%s[]', $args);
        }

        return null;
    }


    /**
     * Get the type of a value
     *
     * @return string
     */
    protected function getTypeOf($value): string
    {
        $type = gettype($value);

        switch ($type) {
            case 'integer':
            case 'boolean':
            case 'string':
                return $type;
            case 'double':
                return 'float';
            case 'array':
                return count(array_filter(array_keys($value), 'is_string')) > 0 ? 'assoc' : 'array';
            case 'object':
                return $value instanceof Traversable ? 'array' :
                    ($value instanceof DateTime ? DateTime::class : 'object');
            default:
                return $type;
        }
    }
}
