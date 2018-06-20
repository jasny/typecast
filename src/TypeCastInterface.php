<?php

namespace Jasny;

/**
 * Interface for type casters
 */
interface TypeCastInterface
{
    /**
     * Create a clone of this typecast object for a different value.
     * 
     * @param mixed $value
     * @return static
     */
    public function value($value): self;
    
    /**
     * Set the display name.
     * This is used in notices.
     * 
     * @param string $name
     * @return $this
     */
    public function setName(string $name);

    /**
     * Get the display name.
     *
     * @return string|null
     */
    public function getName(): ?string;

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
