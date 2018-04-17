<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\ArrayHandler;

/**
 * @covers Jasny\TypeCast\ArrayHandler
 * @covers Jasny\TypeCast\Handler
 */
class ArrayHandlerTest extends TestCase
{
    use \Jasny\TestHelper;
    
    /**
     * @var ArrayHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->handler = new ArrayHandler();
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $newHandler = $this->handler->usingTypecast($typecast);
        
        $this->assertNotSame($this->handler, $newHandler);
        $this->assertAttributeSame($typecast, 'typecast', $newHandler);
        
        $this->assertAttributeSame(null, 'typecast', $this->handler);
    }
    
    public function testForTypeNoCast()
    {
        $ret = $this->handler->forType('array');
        
        $this->assertSame($this->handler, $ret);
        $this->assertAttributeSame(null, 'subtype', $this->handler);
    }
    
    public function testForTypeSubtype()
    {
        $newHandler = $this->handler->forType('string[]');
        
        $this->assertInstanceOf(ArrayHandler::class, $newHandler);
        $this->assertNotSame($this->handler, $newHandler);
        $this->assertAttributeEquals('string', 'subtype', $newHandler);
        
        $this->assertAttributeSame(null, 'subtype', $this->handler);
        
        return $newHandler;
    }
    
    /**
     * @depends testForTypeSubtype
     */
    public function testForTypeWithoutSubtype($handler)
    {
        $newHandler = $handler->forType('array');
        
        $this->assertInstanceOf(ArrayHandler::class, $newHandler);
        $this->assertNotSame($handler, $newHandler);
        $this->assertAttributeSame(null, 'subtype', $newHandler);
        
        $this->assertAttributeEquals('string', 'subtype', $handler);
    }
    
    /**
     * @depends testForTypeSubtype
     */
    public function testForTypeSameSubtype($handler)
    {
        $ret = $handler->forType('string[]');
        
        $this->assertSame($handler, $ret);
        $this->assertAttributeEquals('string', 'subtype', $handler);
    }
    
    /**
     * @expectedException LogicException
     */
    public function testForTypeInvalidArgument()
    {
        $this->handler->forType('string');
    }
    
    public function castProvider()
    {
        $assoc = ['red' => 1, 'green' => 20, 'blue' => 300];
        $object = new \DateTime();
        
        return [
            [null, null],
            [[], []],
            [[1, 20, 300], [1, 20, 300]],
            [['foo', 'bar'], ['foo', 'bar']], 
            [$assoc, $assoc],
            [$assoc, (object)$assoc],
            [$assoc, new \ArrayObject($assoc)],
            [[20], 20],
            [[false], false],
            [[], ''],
            [['foo'], 'foo'],
            [['100, 30, 40'], '100, 30, 40'],
            [[$object], $object],
        ];
    }
    
    /**
     * @dataProvider castProvider
     */
    public function testCast($expected, $value)
    {
        $this->assertSame($expected, $this->handler->cast($value));
    }
    
    public function testCastToSubtype()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        $typecast->expects($this->exactly(3))->method('forValue')
            ->withConsecutive([10], [null], ['foo'])->willReturnSelf();
        $typecast->expects($this->exactly(3))->method('to')->with('bar')
            ->willReturnOnConsecutiveCalls('a', 'b', 'c');
        
        $handler = $this->handler->forType('bar[]')->usingTypecast($typecast);
        
        $result = $handler->cast([10, null, 'foo']);
        $this->assertSame(['a', 'b', 'c'], $result);
    }
    
    public function testCastWithResource()
    {
        if (!function_exists('imagecreate')) {
            $this->markTestSkipped("GD not available. Using gd resource for test.");
        }
        
        $resource = imagecreate(10, 10);
        $ret = @$this->handler->cast($resource);
        
        $this->assertSame($resource, $ret);
        
        $this->assertLastError(E_USER_NOTICE, "Unable to cast gd resource to array");
    }
}
