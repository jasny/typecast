<?php

namespace Jasny\TypeCast;

use Jasny\TypeCast\BooleanHandler;

/**
 * Guess a types
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
     * @param array $types  Possible types
     */
    public function __construct(array $types = [])
    {
        $this->types = $types;
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
        
        $typeguess = clone $this;
        $typeguess->types = $types;
        
        return $typeguess;
    }
    
    /**
     * Guess a type for the value
     * 
     * @param type $value
     * @return array
     */
    public function guessFor($value): array
    {
        $possibleTypes = $this->getPossibleTypes($value);
        $guess = $this->forTypes($possibleTypes)->reduceTypes($value);
        
        return $guess->exactTypes($value) ?: $types;
    }
    
    /**
     * Get possible types based on the value
     * 
     * @param type $value
     */
    protected function getPossibleTypes($value): array
    {
        $type = $this->isAssoc($value) ? 'assoc' : gettype($value);
        
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
            'string' => false,
            'integer' => is_string($value) && !is_numeric($value),
            'float' => is_string($value) && !is_numeric($value),
            'boolean' => is_string($value) && !in_array($value, BooleanHandler::getBooleanStrings()),
            'array' => true,
            'object' => true,
            'resouce' => true,
            'stdClass' => true,
            'DateTime' => is_bool($value) || is_float($value) || (is_string($value) && strtotime($value) === false)
        ];

        return array_udiff($singleTypes, array_keys(array_filter($not)), 'strcasecmp');
    }
    
    /**
     * Get possible types based on a (numeric) array value
     * 
     * @param array $value
     * @return array
     */
    protected function getPossibleArrayTypes(): array
    {
        return array_filter($this->types, function($type) {
            return in_array(strtolower($type), ['array', 'object', 'stdclass'])
                || substr($type, -2) === '[]'
                || (class_exists($type) && is_a(\Traversable::class, $type, true));
        });
    }
    
    /**
     * Get possible types based on associated array or stdClass object.
     * 
     * @param array $value
     * @return array
     */
    protected function getPossibleAssocTypes(): array
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
                || (in_array($type, ['object', 'array']) && $type instanceof \stdClass);
        });
    }
    
    /**
     * Reduce types that superseed others
     * 
     * @param mixed $value
     * @return static
     */
    protected function reduceTypes($value): self
    {
        $types = $this->types;
        
        if (in_array('string', $types) && is_string($value) && strtotime($value) !== false) {
            $types = array_diff($types, ['string']);
        }
        
        if ((in_array('integer', $types) || in_array('float', $types)) && is_numeric($value)) {
            $types = array_diff($types, ['boolean', 'string']);
        }
        
        if (in_array('integer', $types) && in_array('float', $types) && is_string($value) && is_numeric($value)) {
            $types = array_diff($types, [strstr($value, '.') ? 'integer' : 'float']);
        }
        
        return $this->forTypes($types)->removeSuperClasses();
    }
    
    /**
     * Reduce classes and interfaces that superseed others
     * 
     * @param array $types
     * @return static
     */
    protected function removeSuperClasses(): self
    {
        $classes = array_filter($this->types, function($type) {
            return class_exists($type) || interface_exists($type);
        });
        
        $types = array_filter($this->types, function($type) use ($classes) {
            return !in_array($type, $classes) || array_reduce($classes, function($remove, $class) use ($type) {
                return $remove || is_a($type, $class, true);
            }, false);
        });
        
        return $this->forTypes($types);
    }
    
    protected function exactTypes(): array
    {
        return $this->types;
    }
    
    /**
     * Check if value is an associated array or stdClass object
     * 
     * @param type $value
     * @return bool
     */
    protected function isAssoc($value): bool
    {
        return (is_array($value) && count(array_filter(array_keys($value), 'is_string')) > 0)
            || (is_object($value) && get_class($value) === 'stdClass');
    }
}
