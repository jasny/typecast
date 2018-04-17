<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\FloatHandler;

/**
 * @covers Jasny\TypeCast\FloatHandler
 * @covers Jasny\TypeCast\NumberHandler
 * @covers Jasny\TypeCast\Handler
 */
class FloatHandlerTest extends TestCase
{
    use \Jasny\TestHelper;
    
    /**
     * @var FloatHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->handler = new FloatHandler();
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $ret = $this->handler->usingTypecast($typecast);
        $this->assertSame($this->handler, $ret);
    }
    
    public function testForType()
    {
        $ret = $this->handler->forType('float');
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
            [10.44, 10.44],
            [-5.22, -5.22],
            [INF, INF],
            [1.0, 1],
            [1.0, true],
            [0.0, false],
            [100.0, '100'],
            [10.44, '10.44'],
            [-10.44, '-10.44'],
            [0.0, '']
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
     * @expectedExceptionMessage  Unable to cast string "foo" to float
     */
    public function testCastWithRandomString()
    {
        $this->handler->cast('foo');
    }
    
    /**
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast array to float
     */
    public function testCastWithArray()
    {
        $this->handler->cast([10, 20]);
    }
    
    /**
     * Test type casting an array to float
     *
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast stdClass object to float
     */
    public function testCastWithObject()
    {
        $this->handler->cast((object)['foo' => 'bar']);
    }
    
    /**
     * Test type casting an resource to float
     *
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast gd resource to float
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
