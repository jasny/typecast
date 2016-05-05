<?php
/** @ignore */

namespace Jasny\TypeCastTest;

/**
 * A test class.
 * @ignore
 * 
 * @foo
 * @bar  Hello world
 * @blue 22
 */
class FooBar
{
    /**
     * The X is here
     * 
     * @var float
     * @test 123
     * @required
     */
    public $x;
    
    /** @var int */
    public $y;
    
    /**
     * Should not be in here
     * @var string
     */
    protected $no;
    
    /**
     * @var \Ball
     */
    public $ball;
    
    /**
     * @var Bike
     */
    public $bike;
    
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
     * Just a test
     * 
     * @return Book
     */
    public function read()
    {
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return 'foo';
    }
    
    /**
     * Create object from data
     * 
     * @param array $data
     * @return object
     */
    public static function __set_state($data)
    {
        $foobar = new self();
        
        if (isset($data['x'])) $foobar->x = $data['x'];
        if (isset($data['y'])) $foobar->y = $data['y'];
        
        return $foobar;
    }
}
