<?php
/** @ignore */

namespace Jasny\TypeCast\Test;

/**
 * A test class.
 */
class FooBar
{
    /**
     * @var float
     */
    public $x;
    
    /**
     * @var int
     */
    public $y;
    
    /**
     * Class constructor
     * 
     * @param mixed $x
     */
    public function __construct($x = null)
    {
        $this->x = $x;
    }
    
    /**
     * Create object from data
     * 
     * @param array $data
     * @return object
     */
    public static function __set_state(array $data)
    {
        $foobar = new self();
        
        if (isset($data['x'])) $foobar->x = $data['x'];
        if (isset($data['y'])) $foobar->y = $data['y'];
        
        return $foobar;
    }
}
