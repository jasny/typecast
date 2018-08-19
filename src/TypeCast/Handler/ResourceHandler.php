<?php

namespace Jasny\TypeCast\Handler;

use Jasny\TypeCast\Handler;

/**
 * Type cast to a resource
 */
class ResourceHandler extends Handler
{
    /**
     * Get the type what the handler is casting to.
     * 
     * @return string
     */
    protected function getType(): string
    {
        return 'resource';
    }
    
    /**
     * Cast value to a resource
     *
     * @param mixed $value
     * @return resource|mixed
     */
    public function cast($value)
    {
        if (isset($value) && !is_resource($value)) {
            return $this->dontCast($value);
        }
        
        return $value;
    }
}
