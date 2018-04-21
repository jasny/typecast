<?php

namespace Jasny;

use Jasny\TypeCast;
use Jasny\TypeCast\HandlerInterface;

/**
 * @covers Jasny\TypeCast
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

    public function testSetName()
    {
        $typecast = new TypeCast(null, []);

        $ret = $typecast->setName('QUX');
        $this->assertSame($typecast, $ret);
        
        $this->assertAttributeSame('QUX', 'name', $typecast);
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
    
    public function handlerProvider()
    {
        return [
            ['array', TypeCast\ArrayHandler::class],
            ['boolean', TypeCast\BooleanHandler::class],
            ['float', TypeCast\FloatHandler::class],
            ['integer', TypeCast\IntegerHandler::class],
            ['mixed', TypeCast\MixedHandler::class],
            ['object', TypeCast\ObjectHandler::class],
            ['resource', TypeCast\ResourceHandler::class],
            ['string', TypeCast\StringHandler::class],
            ['multiple', TypeCast\MultipleHandler::class]
        ];
    }
    
    /**
     * @dataProvider handlerProvider
     * 
     * @param string $type
     * @param string $class
     */
    public function testGetHandler($type, $class)
    {
        $typecast = new TypeCast();
        
        $handler = $typecast->getHandler($type);
        $this->assertInstanceOf($class, $handler);
    }

    /**
     * @expectedException OutOfBoundsException
     * @expectedExceptionMessage Unable to find handler to cast to 'cow'
     */
    public function testGetHandlerUnknown()
    {
        $typecast = new TypeCast();
        
        $typecast->getHandler('farm', 'cow');
    }
    
    public function testTo()
    {
        $handler = $this->createMock(HandlerInterface::class);
        
        $typecast = new TypeCast('ten', ['integer' => $handler]);
        $typecast->setName('QUX');

        $handler->expects($this->once())->method('forType')->with('integer')->willReturnSelf();
        $handler->expects($this->once())->method('usingTypecast')->with($this->identicalTo($typecast))
            ->willReturnSelf();
        $handler->expects($this->once())->method('withName')->with('QUX')->willReturnSelf();
        $handler->expects($this->once())->method('cast')->with('ten')->willReturn(10);
        
        $ret = $typecast->to('integer');
        $this->assertEquals(10, $ret);
    }

    public function aliasProvider()
    {
        return [
            ['foo', 'integer'],
            ['foo[]', 'integer[]'],
            ['foo|boolean', 'integer|boolean']
        ];
    }
    
    /**
     * @dataProvider aliasProvider
     * 
     * @param string $type
     * @param string $normalType
     */
    public function testAlias($type, $normalType)
    {
        $handler = $this->createMock(HandlerInterface::class);
        
        $typecast = new TypeCast(null, ['integer' => $handler, 'array' => $handler, 'multiple' => $handler]);
        $typecast->alias('foo', 'integer');

        $handler->expects($this->once())->method('forType')->with($normalType)->willReturnSelf();
        $handler->expects($this->once())->method('usingTypecast')->willReturnSelf();
        $handler->expects($this->once())->method('withName')->willReturnSelf();
        $handler->expects($this->once())->method('cast');
        
        $typecast->to($type);
    }
}
