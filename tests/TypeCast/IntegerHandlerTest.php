<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\IntegerHandler;

/**
 * @covers Jasny\TypeCast\IntegerHandler
 * @covers Jasny\TypeCast\NumberHandler
 * @covers Jasny\TypeCast\Handler
 */
class IntegerHandlerTest extends TestCase
{
    use \Jasny\TestHelper;
    
    /**
     * @var IntegerHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->handler = new IntegerHandler();
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $ret = $this->handler->usingTypecast($typecast);
        $this->assertSame($this->handler, $ret);
    }
    
    public function testForType()
    {
        $ret = $this->handler->forType('integer');
        $this->assertSame($this->handler, $ret);
    }
    
    /**
     * @expectedException LogicException
     */
    public function testForTypeInvalid()
    {
        $this->handler->forType('foo');
    }
    
    
    public function castProvider()
    {
        return [
            [null, null],
            [1, 1],
            [0, 0],
            [-1, -1],
            [10, 10.44],
            [1, true],
            [0, false],
            [100, '100'],
            [100, '100.44'],
            [0, '']
        ];
    }
    
    /**
     * @dataProvider castProvider
     */
    public function testCast($expected, $value)
    {
        $this->assertSame($expected, $this->handler->cast($value));
    }
    
    
    /**
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast string "foo" to integer
     */
    public function testCastWithRandomString()
    {
        $this->handler->cast('foo');
    }
    
    /**
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast array to integer
     */
    public function testCastWithArray()
    {
        $this->handler->cast([10, 20]);
    }
    
    /**
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast stdClass object to integer
     */
    public function testCastWithObject()
    {
        $this->handler->cast((object)['foo' => 'bar']);
    }
    
    /**
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast gd resource to integer
     */
    public function testCastWithResource()
    {
        if (!function_exists('imagecreate')) {
            $this->markTestSkipped("GD not available. Using gd resource for test.");
        }
        
        $resource = imagecreate(10, 10);
        $this->handler->cast($resource);
    }
}
