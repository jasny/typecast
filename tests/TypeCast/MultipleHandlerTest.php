<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Jasny\TypeCast;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\MultipleHandler;

/**
 * @covers Jasny\TypeCast\MultipleHandler
 * @covers Jasny\TypeCast\Handler
 */
class MultipleHandlerTest extends TestCase
{
    use \Jasny\TestHelper;

    /**
     * @var TypeCast|MockObject
     */
    protected $typecast;
    
    /**
     * @var MultipleHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->typecast = $this->createMock(TypeCast::class);
        
        $this->handler = (new MultipleHandler())->usingTypecast($this->typecast);
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $handler = new MultipleHandler();
        $newHandler = $handler->usingTypecast($typecast);
        
        $this->assertNotSame($this->handler, $newHandler);
        $this->assertAttributeSame($typecast, 'typecast', $newHandler);
        
        $this->assertAttributeSame(null, 'typecast', $handler);
    }
    
    public function testForType()
    {
        $newHandler = $this->handler->forType('string|integer[]');
        
        $this->assertInstanceOf(MultipleHandler::class, $newHandler);
        $this->assertNotSame($this->handler, $newHandler);
        $this->assertAttributeEquals(['string', 'integer[]'], 'types', $newHandler);
        
        $this->assertAttributeSame([], 'types', $this->handler);
        
        return $newHandler;
    }
    
    /**
     * @depends testForType
     */
    public function testForTypeSameSubtype($handler)
    {
        $ret = $handler->forType('integer[]|string');
        
        $this->assertSame($handler, $ret);
        $this->assertAttributeEquals(['string', 'integer[]'], 'types', $ret);
    }
    
    public function castNopProvider()
    {
        return [
            [null, 'integer|boolean'],
            [1, 'integer|boolean'],
            [true, 'integer|boolean'],
            ['on', 'string|boolean'],
            ['foo', 'string|integer|float']
        ];
    }
    
    /**
     * @dataProvider castNopProvider
     */
    public function testCastNop($value, $type)
    {
        $this->typecast->expects($this->never())->method('forValue');
        $this->typecast->expects($this->never())->method('to');
        
        $actual = $this->handler->forType($type)->cast($value);
        $this->assertSame($value, $actual);
    }
    
    public function testCastReturn()
    {
        $this->typecast->expects($this->once())->method('forValue')->with('10')->willReturnSelf();
        $this->typecast->expects($this->once())->method('to')->with('integer')->willReturn('ten');
        
        $ret = $this->handler->forType('integer')->cast('10');
        $this->assertSame('ten', $ret);
    }
    
    public function castProvider()
    {
        return [
            ['10.0', 'integer|boolean', 'integer'],
            ['1', 'integer|boolean', 'integer'],
            ['on', 'integer|boolean', 'boolean'],
            ['10.0', 'integer|float', 'float'],
            ['10.0', 'string|integer|float', 'float'],
            ['10', 'string|integer|float', 'integer'],
            ['10', 'integer|null', 'integer'],
            ['10', 'array|null', 'array'],
            ['10', 'integer|array|stdClass', 'integer'],
            ['2018-01-03', 'integer|DateTime|string', 'DateTime'],
            ['hello', 'integer|Foo|Foo[]', 'Foo']
        ];
    }
    
    /**
     * @dataProvider castProvider
     */
    public function testCast($value, $types, $expected)
    {
        $this->typecast->expects($this->once())->method('forValue')->with($value)->willReturnSelf();
        $this->typecast->expects($this->once())->method('to')->with($expected);
        
        $this->handler->forType($types)->cast($value);
    }
    
    public function castNopArrayProvider()
    {
        return [
            [['foo', 'bar'], 'string[]|integer[]'],
            [[10, 20], 'string[]|integer[]'],
            [[10, 20], 'integer|integer[]'],
            [[10, 20], 'stdClass|integer[]'],
            [[10, 20], 'Foo|integer[]'],
            [[10, 20], 'DateTime[]|integer[]']
        ];
    }
    
    /**
     * @dataProvider castNopArrayProvider
     */
    public function testCastNopArray($value, $type)
    {
        $this->typecast->expects($this->never())->method('forValue');
        $this->typecast->expects($this->never())->method('to');
        
        $actual = $this->handler->forType($type)->cast($value);
        $this->assertSame($value, $actual);
    }
    
    public function testCastArray()
    {
    }
}
