<?php

namespace Jasny;

/**
 * Interface for type casters
 */
interface TypeCastInterface
{
    /**
     * Class constructor
     *
     * @param mixed $value
     */
    public function __construct($value = null);
    
    /**
     * Get the uncasted value
     * 
     * @return mixed
     */
    public function getValue();
    
    /**
     * Create a clone of this typecast object for a different value.
     * 
     * @param mixed $value
     * @return static
     */
    public function forValue($value): self;
    
    /**
     * Set the display name.
     * This is used in notices.
     * 
     * @param string $name
     * @return $this
     */
    public function setName(string $name);
    
    /**
     * Add a custom alias
     * 
     * @param string $alias
     * @param string $type
     * @return $this
     */
    public function alias(string $alias, string $type);
    
    /**
     * Cast value to specified type
     *
     * @param string $type
     * @return mixed
     */
    public function to(string $type);
}
