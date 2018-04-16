<?php

namespace Jasny\TypeCast;

use Jasny\TypeCastInterface;

/**
 * Type cast to an array
 */
trait ToArray
{
    /**
     * Get the value
     * 
     * @return mixed
     */
    abstract public function getValue();
    
    /**
     * Trigger a warning that the value can't be casted and return $value
     * 
     * @param string $type
     * @param string $explain  Additional message
     * @return mixed
     */
    abstract public function dontCastTo(string $type, string $explain = null);
    
    /**
     * Cast value
     *
     * @param string $type
     * @return mixed
     */
    abstract public function to(string $type);
    
    /**
     * Create a clone of this typecast object for a different value
     * 
     * @param mixed $value
     * @return TypeCastInterface|static
     */
    abstract public function forValue($value): TypeCastInterface;
    
    
    /**
     * Cast value to a array
     *
     * @param string|\Closure $subtype  Type of the array items
     * @return array|mixed
     */
    public function toArray($subtype = null)
    {
        $fn = gettype($this->getValue()) . 'ToArray';
        $array = method_exists($this, $fn) ? $this->$fn() : (array)$this->getValue();
        
        if (is_array($array) && isset($subtype)) {
            $this->castArrayItem($array, $subtype);
        }
        
        return $array;
    }
    
    /**
     * Cast each item in an array
     * 
     * @param array           $array
     * @param string|\Closure $subtype
     */
    protected function castArrayItem(&$array, $subtype)
    {
        foreach ($array as &$item) {
            if ($subtype instanceof \Closure) {
                $item = $subtype($item);
            } else {
                $item = $this->forValue($item)->to($subtype);
            }
        }
    }
    
    
    /**
     * Cast null to a array
     * 
     * @return null
     */
    protected function nullToArray()
    {
        return null;
    }
    
    /**
     * Cast a string to an array
     * 
     * @return array
     */
    protected function stringToArray(): array
    {
        return $this->getValue() === '' ? [] : [$this->getValue()];
    }
    
    /**
     * Cast a resource to a array
     * 
     * @return resource
     */
    protected function resourceToArray()
    {
        return $this->dontCastTo('array');
    }
    
    /**
     * Cast an object to a array
     * 
     * @return array
     */
    protected function objectToArray(): array
    {
        $value = $this->getValue();

        if ($value instanceof \Traversable) {
            $array = iterator_to_array($value);
        } elseif ($value instanceof \stdClass) {
            $array = call_user_func('get_object_vars', $value);
        } else {
            $array = [$value];
        }

        return $array;
    }
}
