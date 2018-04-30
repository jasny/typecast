<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\MixedHandler;

/**
 * @covers \Jasny\TypeCast\MixedHandler
 * @covers \Jasny\TypeCast\Handler
 */
class MixedHandlerTest extends TestCase
{
    /**
     * @var MixedHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->handler = new MixedHandler();
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $ret = $this->handler->usingTypecast($typecast);
        $this->assertSame($this->handler, $ret);
    }
    
    public function testForType()
    {
        $ret = $this->handler->forType('mixed');
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
        $object = new \DateTime();
        
        return [
            [null, null],
            [10, 10],
            ['foo', 'foo'],
            [['a', 'b', 'c'], ['a', 'b', 'c']],
            [$object, $object]
        ];
    }
    
    /**
     * @dataProvider castProvider
     */
    public function testCast($expected, $value)
    {
        $this->assertSame($expected, $this->handler->cast($value));
    }
    
    public function testCastWithResource()
    {
        if (!function_exists('imagecreate')) {
            $this->markTestSkipped("GD not available. Using gd resource for test.");
        }
        
        $resource = imagecreate(10, 10);
        
        $ret = $this->handler->cast($resource);
        $this->assertSame($resource, $ret);
    }
}
