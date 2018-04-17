<?php

namespace Jasny;

use Jasny\TypeCast;
use Jasny\TypeCast\HandlerInterface;

/**
 * Tests for TypeCast and all handlers
 */
class TypeCastTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test factory method
     */
    public function testValue()
    {
        $typecast = TypeCast::value('abc123');
        
        $this->assertInstanceOf(TypeCast::class, $typecast);
        $this->assertAttributeSame('abc123', 'value', $typecast);
    }

    public function testAlias()
    {
        $handler = $this->createMock(HandlerInterface::class);
        
        $typecast = new TypeCast(null, ['integer' => $handler]);
        $typecast->alias('foo', 'integer');

        $handler->expects($this->once())->method('forType')->with('integer')->willReturnSelf();
        $handler->expects($this->once())->method('usingTypecast')->with($this->identicalTo($typecast))
            ->willReturnSelf();
        $handler->expects($this->once())->method('withName')->with(null)->willReturnSelf();
        $handler->expects($this->once())->method('cast')->willReturn(10);
        
        $ret = $typecast->to('foo');
        $this->assertEquals(10, $ret);
    }

    public function testSetName()
    {
        $typecast = new TypeCast(null, []);

        $ret = $typecast->setName('QUX');
        $this->assertSame($typecast, $ret);
        
        $this->assertAttributeSame('QUX', 'name', $typecast);
        
        return $typecast;
    }

    public function testForValue()
    {
        $handler = $this->createMock(HandlerInterface::class);
        
        $typecast = new TypeCast('123', ['foo' => $handler]);
        $typecast->setName('hello');
        
        $copy = $typecast->forValue('abc');
        
        $this->assertNotSame($typecast, $copy);
        
        $this->assertInstanceOf(TypeCast::class, $copy);
        $this->assertAttributeEquals('abc', 'value', $copy);
        $this->assertAttributeSame(['foo' => $handler], 'handlers', $copy);
        $this->assertAttributeSame('hello', 'name', $copy);
        
        $this->assertAttributeEquals('123', 'value', $typecast);
    }
}
