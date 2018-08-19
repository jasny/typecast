<?php
/** @ignore */

namespace Jasny\TypeCast\Test;

/**
 * A test class.
 */
class Foo
{
    /**
     * @var mixed
     */
    public $data;
    
    
    /**
     * Create object from data
     * 
     * @param mixed $data
     * @return object
     */
    public static function __set_state($data)
    {
        $foobar = new self();
        $foobar->data = $data;
        
        return $foobar;
    }
}
