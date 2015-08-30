<?php

namespace Jasny;

require_once 'support/class.php';

/**
 * Tests for Jasny\TypeCast.
 * 
 * @package Test
 * @author Arnold Daniels
 */
class TypeCastingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test type casting to a string
     */
    public function testToString()
    {
        $this->assertNull(TypeCast::toString(null));
        $this->assertSame('100', TypeCast::toString('100'));
        $this->assertSame('', TypeCast::toString(''));
        $this->assertSame('1', TypeCast::toString(1));
        $this->assertSame('1', TypeCast::toString(true));
        $this->assertSame('', TypeCast::toString(false));        
    }
    
    /**
     * Test type casting an object with `__toString` to a string
     */
    public function testToString_Stringable()
    {
        $foobar = new TypeCastTest\FooBar();  // Implements __toString
        $this->assertSame('foo', TypeCast::toString($foobar));
    }
    
    /**
     * Test type casting an DateTime to a string
     */
    public function testToString_DateTime()
    {
        $date = new \DateTime("2014-12-31 23:15 UTC");
        $this->assertSame('2014-12-31T23:15:00+00:00', TypeCast::toString($date));
    }
    
    /**
     * Test type casting an array to a string
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to a string
     */
    public function testToString_Array()
    {
        TypeCast::toString([10, 20]);
    }
    
    /**
     * Test type casting an array to a string
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a stdClass object to a string
     */
    public function testToString_Object()
    {
        TypeCast::toString((object)['foo' => 'bar']);
    }
    
    /**
     * Test type casting an resource to a string
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to a string
     */
    public function testToString_Resource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::toString(imagecreate(10, 10));
    }
    
    
    /**
     * Test type casting for boolean
     */
    public function testToBoolean()
    {
        $this->assertNull(TypeCast::toBoolean(null));
        
        $this->assertSame(true, TypeCast::toBoolean(true));
        $this->assertSame(true, TypeCast::toBoolean(1));
        $this->assertSame(true, TypeCast::toBoolean(-1));
        $this->assertSame(true, TypeCast::toBoolean(10));
        $this->assertSame(true, TypeCast::toBoolean('1'));
        $this->assertSame(true, TypeCast::toBoolean('true'));
        $this->assertSame(true, TypeCast::toBoolean('yes'));
        $this->assertSame(true, TypeCast::toBoolean('on'));
                
        $this->assertSame(false, TypeCast::toBoolean(false));
        $this->assertSame(false, TypeCast::toBoolean(0));
        $this->assertSame(false, TypeCast::toBoolean('0'));
        $this->assertSame(false, TypeCast::toBoolean('false'));
        $this->assertSame(false, TypeCast::toBoolean('no'));
        $this->assertSame(false, TypeCast::toBoolean('off'));
    }
    
    /**
     * Test type casting an array to a boolean
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to a boolean
     */
    public function testToBoolean_Array()
    {
        TypeCast::toBoolean([10, 20]);
    }
    
    /**
     * Test type casting an array to a boolean
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a stdClass object to a boolean
     */
    public function testToBoolean_Object()
    {
        TypeCast::toBoolean((object)['foo' => 'bar']);
    }
    
    /**
     * Test type casting an resource to a boolean
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to a boolean
     */
    public function testToBoolean_Resource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::toBoolean(imagecreate(10, 10));
    }
    
    
    /**
     * Test type casting for integer
     */
    public function testToInteger()
    {
        $this->assertNull(TypeCast::toInteger(null));

        $this->assertSame(1, TypeCast::toInteger(1));
        $this->assertSame(0, TypeCast::toInteger(0));
        $this->assertSame(-1, TypeCast::toInteger(-1));
        $this->assertSame(10, TypeCast::toInteger(10.44));
        $this->assertSame(1, TypeCast::toInteger(true));
        $this->assertSame(0, TypeCast::toInteger(false));
        $this->assertSame(100, TypeCast::toInteger('100'));
        $this->assertSame(100, TypeCast::toInteger('100.44'));
        $this->assertSame(0, TypeCast::toInteger(''));
    }
    
    /**
     * Test type casting a string to a integer
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to a integer
     */
    public function testToInteger_String()
    {
        TypeCast::toInteger('foo');
    }
    
    /**
     * Test type casting an array to a integer
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to a integer
     */
    public function testToInteger_Array()
    {
        TypeCast::toInteger([10, 20]);
    }
    
    /**
     * Test type casting an array to a integer
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a stdClass object to a integer
     */
    public function testToInteger_Object()
    {
        TypeCast::toInteger((object)['foo' => 'bar']);
    }
    
    /**
     * Test type casting an resource to a integer
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to a integer
     */
    public function testToInteger_Resource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::toInteger(imagecreate(10, 10));
    }
    
    
    /**
     * Test type casting for float
     */
    public function testToFloat()
    {
        $this->assertNull(TypeCast::toFloat(null));

        $this->assertSame(10.44, TypeCast::toFloat(10.44));
        $this->assertSame(-5.22, TypeCast::toFloat(-5.22));
        $this->assertSame(INF, TypeCast::toFloat(INF));
        $this->assertSame(1.0, TypeCast::toFloat(1));
        $this->assertSame(1.0, TypeCast::toFloat(true));
        $this->assertSame(0.0, TypeCast::toFloat(false));
        $this->assertSame(100.0, TypeCast::toFloat('100'));
        $this->assertSame(10.44, TypeCast::toFloat('10.44'));
        $this->assertSame(-10.44, TypeCast::toFloat('-10.44'));
        $this->assertSame(0.0, TypeCast::toFloat(''));
    }

    /**
     * Test type casting a string to a float
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to a float
     */
    public function testToFloat_String()
    {
        TypeCast::toFloat('foo');
    }
    
    /**
     * Test type casting an array to a float
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to a float
     */
    public function testToFloat_Array()
    {
        TypeCast::toFloat([10, 20]);
    }
    
    /**
     * Test type casting an array to a float
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a stdClass object to a float
     */
    public function testToFloat_Object()
    {
        TypeCast::toFloat((object)['foo' => 'bar']);
    }
    
    /**
     * Test type casting an resource to a float
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to a float
     */
    public function testToFloat_Resource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::toFloat(imagecreate(10, 10));
    }
    
    
    /**
     * Test type casting for array
     */
    public function testToArray()
    {
        $this->assertNull(TypeCast::toArray(null));

        $numeric = [1, 20, 300];
        $this->assertSame($numeric, TypeCast::toArray($numeric));
        
        $assoc = ['red' => 1, 'green' => 20, 'blue' => 300];
        $this->assertSame($assoc, TypeCast::toArray($assoc));
        $this->assertSame($assoc, TypeCast::toArray((object)$assoc));
        
        $this->assertSame([20], TypeCast::toArray(20));
        $this->assertSame([false], TypeCast::toArray(false));
        $this->assertSame([], TypeCast::toArray(''));
        $this->assertSame(['foo'], TypeCast::toArray('foo'));
        $this->assertSame(['100, 30, 40'], TypeCast::toArray('100, 30, 40'));
    }
    
    /**
     * Test type casting an resource to an array
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to an array
     */
    public function testToArray_Resource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::toArray(imagecreate(10, 10));
    }

    
    /**
     * Test type casting for object
     */
    public function testToObject()
    {
        $this->assertNull(TypeCast::toObject(null));
            
        $assoc = ['red' => 1, 'green' => 20, 'blue' => 300];
        $obj = (object)$assoc;
        
        $castedObj = TypeCast::toObject($obj);
        $this->assertInternalType('object', $castedObj);
        $this->assertSame($obj, $castedObj);
        
        $castedAssoc = TypeCast::toObject($obj);
        $this->assertInternalType('object', $castedAssoc);
        $this->assertEquals($obj, $castedAssoc);
    }
    
    /**
     * Test the notice when type casting a scalar value to an object
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a string to an object
     */
    public function testToObject_Scalar()
    {
        TypeCast::toObject('foo');
    }
    
    /**
     * Test type casting an resource to an object
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to an object
     */
    public function testToObject_Resource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::toObject(imagecreate(10, 10));
    }


    /**
     * Test type casting an resource to an object
     */
    public function testToResource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        
        $this->assertNull(null, TypeCast::toResource(null));
        
        $resource = imagecreate(10, 10);
        $this->assertSame($resource, TypeCast::toResource($resource));
    }

    /**
     * Test type casting an resource to an object
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a string to a resource
     */
    public function testToResource_String()
    {
        TypeCast::toResource('foo');
    }

    
    /**
     * Test type casting for DateTime
     */
    public function testToDateTime()
    {
        $this->assertNull(TypeCast::toClass(null, 'DateTime'));

        $date = TypeCast::toClass('2014-06-01T01:15:00+00:00', 'DateTime');
        $this->assertInstanceOf('DateTime', $date);
        $this->assertSame('2014-06-01T01:15:00+00:00', $date->format('c'));
    }
    
    /**
     * Test type casting for custom class
     */
    public function testToClass()
    {
        $this->assertNull(TypeCast::toClass(null, 'Jasny\TypeCastTest\FooBar'));
        $this->assertNull(TypeCast::toClass(null, 'Jasny\TypeCastTest\NotExistent'));

        $foobar = new TypeCastTest\FooBar('abc123');
        $this->assertSame($foobar, TypeCast::toClass($foobar, 'Jasny\TypeCastTest\FooBar'));
        
        $castedInt = TypeCast::toClass(22, 'Jasny\TypeCastTest\FooBar');
        $this->assertInstanceOf('Jasny\TypeCastTest\FooBar', $castedInt);
        $this->assertSame(22, $castedInt->x);
    }
    
    /**
     * Test the exception when type casting for custom class
     *
     * @expectedException         Exception
     * @expectedExceptionMessage  Invalid type 'Jasny\TypeCastTest\NotExistent'
     */
    public function testToClass_Exception()
    {
        TypeCast::toClass(22, 'Jasny\TypeCastTest\NotExistent');
    }
    
    
    /**
     * Test type casting for typed array
     */
    public function testToTypedArray_Int()
    {
        $this->assertNull(TypeCast::toArray(null, 'int'));
        
        $this->assertSame([], TypeCast::toArray([], 'int'));
        $this->assertSame([], TypeCast::toArray(''));
        $this->assertSame([1, 20, 300], TypeCast::toArray([1, 20, 300], 'int'));
        
        $assoc = ['red' => 1, 'green' => 20, 'blue' => 300];
        $this->assertSame($assoc, TypeCast::toArray($assoc, 'int'));
        $this->assertSame($assoc, TypeCast::toArray((object)$assoc, 'int'));
        
        $this->assertSame([1, 20, -300], TypeCast::toArray(['1', '20.3', '-300'], 'int'));
        $this->assertSame([20], TypeCast::toArray(20, 'int'));
        $this->assertSame([10], TypeCast::toArray(10.44, 'int'));
        $this->assertSame([100], TypeCast::toArray('100', 'int'));
        $this->assertSame([0], TypeCast::toArray(false, 'int'));
    }
    
    /**
     * Test type casting for typed array with a class
     */
    public function testToTypedArray_Class()
    {
        $this->assertNull(TypeCast::toArray(null, 'Jasny\TypeCastTest\FooBar'));
        
        $this->assertSame([], TypeCast::toArray([], 'Jasny\TypeCastTest\FooBar'));
        $this->assertSame([], TypeCast::toArray('', 'Jasny\TypeCastTest\FooBar'));
        
        // Array
        $castArr = TypeCast::toArray([1, 20, 300], 'Jasny\TypeCastTest\FooBar');
        $this->assertInternalType('array', $castArr);
        $this->assertEquals([0, 1, 2], array_keys($castArr));
        
        $this->assertInstanceOf('Jasny\TypeCastTest\FooBar', $castArr[0]);
        $this->assertInstanceOf('Jasny\TypeCastTest\FooBar', $castArr[1]);
        $this->assertInstanceOf('Jasny\TypeCastTest\FooBar', $castArr[2]);
        
        $this->assertSame(1, $castArr[0]->x);
        $this->assertSame(20, $castArr[1]->x);
        $this->assertSame(300, $castArr[2]->x);

        // value object
        $obj = (object)['red' => 1, 'green' => 20, 'blue' => 300];
        $castObj = TypeCast::toArray($obj, 'Jasny\TypeCastTest\FooBar');
        $this->assertInternalType('array', $castObj);
        $this->assertEquals(['red', 'green', 'blue'], array_keys($castObj));

        $this->assertInstanceOf('Jasny\TypeCastTest\FooBar', $castObj['red']);
        $this->assertInstanceOf('Jasny\TypeCastTest\FooBar', $castObj['green']);
        $this->assertInstanceOf('Jasny\TypeCastTest\FooBar', $castObj['blue']);
        
        $this->assertSame(1, $castObj['red']->x);
        $this->assertSame(20, $castObj['green']->x);
        $this->assertSame(300, $castObj['blue']->x);
        
        // Scalar
        $castInt = TypeCast::toArray(20, 'Jasny\TypeCastTest\FooBar');
        $this->assertInternalType('array', $castInt);
        $this->assertEquals([0], array_keys($castInt));
        
        $this->assertInstanceOf('Jasny\TypeCastTest\FooBar', $castInt[0]);
        $this->assertSame(20, $castInt[0]->x);
    }
    
    /**
     * Test type casting a string to an array of integers
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to a integer
     */
    public function testToTypedArray_String()
    {
        TypeCast::toArray('foo', 'int');
    }
    
    
    /**
     * Test `cast` method
     */
    public function testCast()
    {
        $this->assertSame('123', TypeCast::cast(123, 'string'));    
        $this->assertSame(true, TypeCast::cast('1', 'boolean'));
        $this->assertSame(123, TypeCast::cast('123', 'integer'));
        $this->assertSame(123.6, TypeCast::cast('123.6', 'float'));
        $this->assertSame([123], TypeCast::cast(123, 'array'));
        $this->assertEquals((object)['red' => 123], TypeCast::cast(['red' => 123], 'object'));
        
        $this->assertSame(true, TypeCast::cast('1', 'bool'));
        $this->assertSame(123, TypeCast::cast('123', 'int'));
        
        $date = TypeCast::cast('2014-06-01T01:15:00+00:00', 'DateTime');
        $this->assertInstanceOf('DateTime', $date);
        
        $this->assertSame([123], TypeCast::cast('123.0', 'int[]'));
        
        $dateArr = TypeCast::cast('2014-06-01T01:15:00+00:00', 'DateTime[]');
        $this->assertInternalType('array', $dateArr);
        $this->assertInstanceOf('DateTime', reset($dateArr));
    }
    
    /**
     * Test factory method
     */
    public function testValue()
    {
        $typecast = TypeCast::value('abc123');
        
        $refl = new \ReflectionProperty('Jasny\TypeCast', 'value');
        $refl->setAccessible(true);
        
        $this->assertSame('abc123', $refl->getValue($typecast));
    }

    /**
     * Test `to` method
     *
     * @depends testValue
     * @depends testCast
     */
    public function testTo()
    {
        $typecast = TypeCast::value('123');
        $this->assertSame(123, $typecast->to('int'));
    }
}

