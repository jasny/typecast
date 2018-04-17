<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
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
     * @var MultipleHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->handler = new MultipleHandler();
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $newHandler = $this->handler->usingTypecast($typecast);
        
        $this->assertNotSame($this->handler, $newHandler);
        $this->assertAttributeSame($typecast, 'typecast', $newHandler);
        
        $this->assertAttributeSame(null, 'typecast', $this->handler);
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
    
    public function toMultipleProvider()
    {
        return [
            [null, null],
            [1, 1],
            [true, true]
        ];
    }
    
    /**
     * @dataProvider toMultipleProvider
     * 
     * @param mixed $expected
     * @param mixed $value
     */
    public function testToMultiple($expected, $value)
    {
        return $this->markTestSkipped();
        
        $actual = TypeCast::value($value)->toMultiple(['int', 'boolean']);
        $this->assertSame($expected, $actual);
    }
    
    /**
     * Test type casting presenting multiple types
     */
    public function testToMultipleNull()
    {
        return $this->markTestSkipped();
        
        $this->assertSame(10, TypeCast::value('10')->toMultiple(['int', 'null']));
    }
    
    /**
     * Test type casting presenting multiple types with no matching type
     * 
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast string "foo" to integer|boolean
     */
    public function testToMultipleNoMatch()
    {
        return $this->markTestSkipped();
        
        TypeCast::value('foo')->toMultiple(['int', 'boolean']);
    }
    
    /**
     * Get type casting presenting multiple types
     */
    public function testToMultipleArray()
    {
        return $this->markTestSkipped();
        
        $this->assertSame([true, true, false], TypeCast::value([1, 'on', false])->toMultiple(['int', 'bool[]']));
        $this->assertSame([true, true, false], TypeCast::value([1, 'on', false])->toMultiple(['stdClass[]', 'bool[]']));
    }
    
    /**
     * Test type casting presenting multiple types
     */
    public function testToMultipleTypedArray()
    {
        return $this->markTestSkipped();
        
        $this->assertSame([1, true, false], TypeCast::value([1, true, false])->toMultiple(['int[]', 'bool[]']));
    }

    
    /**
     * Test type casting presenting multiple types casting a type to an array
     */
    public function testToMultipleTypeToTypedArray()
    {
        return $this->markTestSkipped();
        
        $this->assertSame([10], TypeCast::value(10)->toMultiple(['string[]', 'int[]']));
        $this->assertSame(['foo'], TypeCast::value('foo')->toMultiple(['string[]', 'int[]']));
    }
    
    /**
     * Test type casting presenting multiple types which are type|type[]
     */
    public function testToMultipleTypeOrArray()
    {
        return $this->markTestSkipped();
        
        $this->assertSame(10, TypeCast::value('10')->toMultiple(['int', 'int[]']));
        $this->assertSame([10, 20, 30], TypeCast::value(['10', 20, '30'])->toMultiple(['int', 'int[]']));
    }

    /**
     * Test type casting presenting multiple types by elminiting typed arrays
     */
    public function testToMultipleEliminateArray()
    {
        return $this->markTestSkipped();
        
        $array = ['foo', false, 'bar', 10];
        $this->assertSame($array, TypeCast::value($array)->toMultiple(['array', 'stdClass[]', 'int[]']));
    }

    /**
     * Test type casting presenting multiple array types which are type|type[] with no matching type
     * 
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast string "rock" to a integer
     */
    public function testToMultipleTypeOrArrayNoMatch()
    {
        return $this->markTestSkipped();
        
        TypeCast::value('rock')->toMultiple(['int', 'int[]']);
    }
    
    /**
     * Test type casting presenting multiple array types which are type|type[] with no matching type
     * 
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast a boolean to string|integer|string[]|integer[]
     */
    public function testToMultipleTypeOrArrayNoMatch2()
    {
        return $this->markTestSkipped();
        
        TypeCast::value(true)->toMultiple(['string', 'int', 'string[]', 'int[]']);
    }
    
    /**
     * Test type casting presenting multiple array types with no matching type
     * 
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast string "rock" to integer[]|boolean[]
     */
    public function testToMultipleTypedArrayNotArray()
    {
        return $this->markTestSkipped();
        
        TypeCast::value('rock')->toMultiple(['integer[]', 'boolean[]']);
    }
    
    /**
     * Test type casting presenting multiple array types with no matching type
     * 
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast string "rock" to integer|boolean
     */
    public function testToMultipleTypedArrayNoMatch()
    {
        return $this->markTestSkipped();
        
        TypeCast::value([1, 'on', false, 'rock'])->toMultiple(['integer[]', 'boolean[]']);
    }
}
