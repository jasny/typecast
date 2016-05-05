<?php

namespace Jasny;

use Jasny\TypeCastTest\FooBar;

/**
 * @covers \Jasny\TypeCast
 */
class TypeCastingTest extends \PHPUnit_Framework_TestCase
{
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

    public function testAlias()
    {
        $typecast = TypeCast::value('123');
        $typecast->alias('foo', 'integer');
        
        $this->assertSame(123, $typecast->to('foo'));
    }
    
    public function testDontCastToReturnValue()
    {
        $object = (object)['foo' => 'bar'];
        $typecast = TypeCast::value($object);
        
        $this->assertSame($object, @$typecast->dontCastTo('foo'));
    }
    
    
    /**
     * Test type casting to mixed
     */
    public function testToMixed()
    {
        $object = (object)['foo' => 'bar'];
        $this->assertSame($object, TypeCast::value($object)->toMixed());
    }
    
    /**
     * Test type casting to a string
     */
    public function testToString()
    {
        $this->assertNull(TypeCast::value(null)->toString());
        $this->assertSame('100', TypeCast::value('100')->toString());
        $this->assertSame('', TypeCast::value('')->toString());
        $this->assertSame('1', TypeCast::value(1)->toString());
        $this->assertSame('1', TypeCast::value(true)->toString());
        $this->assertSame('', TypeCast::value(false)->toString());        
    }
    
    /**
     * Test type casting an object with `__toString` to a string
     */
    public function testToStringWithStringable()
    {
        $foobar = new TypeCastTest\FooBar();  // Implements __toString
        $this->assertSame('foo', TypeCast::value($foobar)->toString());
    }
    
    /**
     * Test type casting an DateTime to a string
     */
    public function testToStringWithDateTime()
    {
        $date = new \DateTime("2014-12-31 23:15 UTC");
        $this->assertSame('2014-12-31T23:15:00+00:00', TypeCast::value($date)->toString());
    }
    
    /**
     * Test type casting an array to a string
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to a string
     */
    public function testToStringWithArray()
    {
        TypeCast::value([10, 20])->toString();
    }
    
    /**
     * Test type casting an array to a string
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a stdClass object to a string
     */
    public function testToStringWithObject()
    {
        TypeCast::value((object)['foo' => 'bar'])->toString();
    }
    
    /**
     * Test type casting an resource to a string
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to a string
     */
    public function testToStringWithResource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::value(imagecreate(10, 10))->toString();
    }
    
    
    /**
     * Test type casting for boolean
     */
    public function testToBoolean()
    {
        $this->assertNull(TypeCast::value(null)->toBoolean());
        
        $this->assertSame(true, TypeCast::value(true)->toBoolean());
        $this->assertSame(true, TypeCast::value(1)->toBoolean());
        $this->assertSame(true, TypeCast::value(-1)->toBoolean());
        $this->assertSame(true, TypeCast::value(10)->toBoolean());
        $this->assertSame(true, TypeCast::value('1')->toBoolean());
        $this->assertSame(true, TypeCast::value('true')->toBoolean());
        $this->assertSame(true, TypeCast::value('yes')->toBoolean());
        $this->assertSame(true, TypeCast::value('on')->toBoolean());
                
        $this->assertSame(false, TypeCast::value(false)->toBoolean());
        $this->assertSame(false, TypeCast::value(0)->toBoolean());
        $this->assertSame(false, TypeCast::value('0')->toBoolean());
        $this->assertSame(false, TypeCast::value('false')->toBoolean());
        $this->assertSame(false, TypeCast::value('no')->toBoolean());
        $this->assertSame(false, TypeCast::value('off')->toBoolean());
    }
    
    /**
     * Test type casting an random string to a boolean
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to a boolean
     */
    public function testToBooleanWithRandomString()
    {
        TypeCast::value('foo')->toBoolean();
    }
    
    /**
     * Test type casting an array to a boolean
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to a boolean
     */
    public function testToBooleanWithArray()
    {
        TypeCast::value([10, 20])->toBoolean();
    }
    
    /**
     * Test type casting an array to a boolean
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a stdClass object to a boolean
     */
    public function testToBooleanWithObject()
    {
        TypeCast::value((object)['foo' => 'bar'])->toBoolean();
    }
    
    /**
     * Test type casting an resource to a boolean
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to a boolean
     */
    public function testToBooleanWithResource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::value(imagecreate(10, 10))->toBoolean();
    }
    
    
    /**
     * Test type casting for integer
     */
    public function testToInteger()
    {
        $this->assertNull(TypeCast::value(null)->toInteger());

        $this->assertSame(1, TypeCast::value(1)->toInteger());
        $this->assertSame(0, TypeCast::value(0)->toInteger());
        $this->assertSame(-1, TypeCast::value(-1)->toInteger());
        $this->assertSame(10, TypeCast::value(10.44)->toInteger());
        $this->assertSame(1, TypeCast::value(true)->toInteger());
        $this->assertSame(0, TypeCast::value(false)->toInteger());
        $this->assertSame(100, TypeCast::value('100')->toInteger());
        $this->assertSame(100, TypeCast::value('100.44')->toInteger());
        $this->assertSame(0, TypeCast::value('')->toInteger());
    }
    
    /**
     * Test type casting a string to a integer
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to a integer
     */
    public function testToIntegerWithString()
    {
        TypeCast::value('foo')->toInteger();
    }
    
    /**
     * Test type casting an array to a integer
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to a integer
     */
    public function testToIntegerWithArray()
    {
        TypeCast::value([10, 20])->toInteger();
    }
    
    /**
     * Test type casting an array to a integer
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a stdClass object to a integer
     */
    public function testToIntegerWithObject()
    {
        TypeCast::value((object)['foo' => 'bar'])->toInteger();
    }
    
    /**
     * Test type casting an resource to a integer
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to a integer
     */
    public function testToIntegerWithResource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::value(imagecreate(10, 10))->toInteger();
    }
    
    
    /**
     * Test type casting for float
     */
    public function testToFloat()
    {
        $this->assertNull(TypeCast::value(null)->toFloat());

        $this->assertSame(10.44, TypeCast::value(10.44)->toFloat());
        $this->assertSame(-5.22, TypeCast::value(-5.22)->toFloat());
        $this->assertSame(INF, TypeCast::value(INF)->toFloat());
        $this->assertSame(1.0, TypeCast::value(1)->toFloat());
        $this->assertSame(1.0, TypeCast::value(true)->toFloat());
        $this->assertSame(0.0, TypeCast::value(false)->toFloat());
        $this->assertSame(100.0, TypeCast::value('100')->toFloat());
        $this->assertSame(10.44, TypeCast::value('10.44')->toFloat());
        $this->assertSame(-10.44, TypeCast::value('-10.44')->toFloat());
        $this->assertSame(0.0, TypeCast::value('')->toFloat());
    }

    /**
     * Test type casting a string to a float
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to a float
     */
    public function testToFloatWithString()
    {
        TypeCast::value('foo')->toFloat();
    }
    
    /**
     * Test type casting an array to a float
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to a float
     */
    public function testToFloatWithArray()
    {
        TypeCast::value([10, 20])->toFloat();
    }
    
    /**
     * Test type casting an array to a float
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a stdClass object to a float
     */
    public function testToFloatWithObject()
    {
        TypeCast::value((object)['foo' => 'bar'])->toFloat();
    }
    
    /**
     * Test type casting an resource to a float
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to a float
     */
    public function testToFloatWithResource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::value(imagecreate(10, 10))->toFloat();
    }
    
    
    /**
     * Test type casting for array
     */
    public function testToArray()
    {
        $this->assertNull(TypeCast::value(null)->toArray());

        $numeric = [1, 20, 300];
        $this->assertSame($numeric, TypeCast::value($numeric)->toArray());
        
        $assoc = ['red' => 1, 'green' => 20, 'blue' => 300];
        $this->assertSame($assoc, TypeCast::value($assoc)->toArray());
        $this->assertSame($assoc, TypeCast::value((object)$assoc)->toArray());
        
        $this->assertSame([20], TypeCast::value(20)->toArray());
        $this->assertSame([false], TypeCast::value(false)->toArray());
        $this->assertSame([], TypeCast::value('')->toArray());
        $this->assertSame(['foo'], TypeCast::value('foo')->toArray());
        $this->assertSame(['100, 30, 40'], TypeCast::value('100, 30, 40')->toArray());
    }
    
    /**
     * Test type casting an resource to an array
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to an array
     */
    public function testToArrayWithResource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::value(imagecreate(10, 10))->toArray();
    }

    
    /**
     * Test type casting for object
     */
    public function testToObject()
    {
        $this->assertNull(TypeCast::value(null)->toObject());
            
        $assoc = ['red' => 1, 'green' => 20, 'blue' => 300];
        $obj = (object)$assoc;
        
        $castedObj = TypeCast::value($obj)->toObject();
        $this->assertInternalType('object', $castedObj);
        $this->assertSame($obj, $castedObj);
        
        $castedAssoc = TypeCast::value($obj)->toObject();
        $this->assertInternalType('object', $castedAssoc);
        $this->assertEquals($obj, $castedAssoc);
    }
    
    /**
     * Test the notice when type casting a scalar value to an object
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to an object
     */
    public function testToObjectWithScalar()
    {
        TypeCast::value('foo')->toObject();
    }
    
    /**
     * Test type casting an resource to an object
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a gd resource to an object
     */
    public function testToObjectWithResource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        TypeCast::value(imagecreate(10, 10))->toObject();
    }


    /**
     * Test type casting an resource to an object
     */
    public function testToResource()
    {
        if (!function_exists('imagecreate')) $this->markTestSkipped("GD not available. Using gd resource for test.");
        
        $this->assertNull(null, TypeCast::value(null)->toResource());
        
        $resource = imagecreate(10, 10);
        $this->assertSame($resource, TypeCast::value($resource)->toResource());
    }

    /**
     * Test type casting an resource to an object
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to a resource
     */
    public function testToResourceWithString()
    {
        TypeCast::value('foo')->toResource();
    }

    
    /**
     * Test type casting for DateTime
     */
    public function testToDateTime()
    {
        $this->assertNull(TypeCast::value(null)->toClass('DateTime'));

        $date = TypeCast::value('2014-06-01T01:15:00+00:00')->toClass('DateTime');
        $this->assertInstanceOf('DateTime', $date);
        $this->assertSame('2014-06-01T01:15:00+00:00', $date->format('c'));
    }
    
    /**
     * Test type casting for custom class
     */
    public function testToClass()
    {
        $this->assertNull(TypeCast::value(null)->toClass(FooBar::class));
        $this->assertNull(TypeCast::value(null)->toClass('Jasny\TypeCastTest\NonExistent'));

        $foobar = new FooBar('abc123');
        $this->assertSame($foobar, TypeCast::value($foobar)->toClass(FooBar::class));
        
        $castedInt = TypeCast::value(22)->toClass(FooBar::class);
        $this->assertInstanceOf(FooBar::class, $castedInt);
        $this->assertSame(22, $castedInt->x);
        
        $castedArray = TypeCast::value(['x' => 10, 'y' => 12])->toClass(FooBar::class);
        $this->assertInstanceOf(FooBar::class, $castedArray);
        $this->assertSame(10, $castedArray->x);
        $this->assertSame(12, $castedArray->y);
    }
    
    /**
     * Test type casting to stdClass
     */
    public function testToStdClass()
    {
        $object = (object)['foo' => 'bar'];
        $this->assertSame($object, TypeCast::value($object)->toClass('stdClass'));
        
        $this->assertEquals($object, TypeCast::value(['foo' => 'bar'])->toClass('stdClass'));
    }
    
    /**
     * Test type casting to stdClass
     */
    public function testToStdClassWithObject()
    {
        $foobar = new FooBar();
        $foobar->x = 'abc123';
        $foobar->y = 123;
        
        $expected = (object)['x' => 'abc123', 'y' => 123, 'ball' => null, 'bike' => null];
        $this->assertEquals($expected, TypeCast::value($foobar)->toClass('stdClass'));
    }
    
    /**
     * Test the exception when type casting for custom class
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast a integer to a Jasny\TypeCastTest\NonExistent object: Class not found
     */
    public function testToClassException()
    {
        TypeCast::value(22)->toClass('Jasny\TypeCastTest\NonExistent');
    }
    
    
    /**
     * Test type casting for typed array
     */
    public function testToTypedArrayWithInt()
    {
        $this->assertNull(TypeCast::value(null)->toArray('int'));
        
        $this->assertSame([], TypeCast::value([])->toArray('int'));
        $this->assertSame([], TypeCast::value('')->toArray('int'));
        $this->assertSame([1, 20, 300], TypeCast::value([1, 20, 300])->toArray('int'));
        
        $assoc = ['red' => 1, 'green' => 20, 'blue' => 300];
        $this->assertSame($assoc, TypeCast::value($assoc)->toArray('int'));
        $this->assertSame($assoc, TypeCast::value((object)$assoc)->toArray('int'));
        
        $this->assertSame([1, 20, -300], TypeCast::value(['1', '20.3', '-300'])->toArray('int'));
        $this->assertSame([20], TypeCast::value(20)->toArray('int'));
        $this->assertSame([10], TypeCast::value(10.44)->toArray('int'));
        $this->assertSame([100], TypeCast::value('100')->toArray('int'));
        $this->assertSame([0], TypeCast::value(false)->toArray('int'));
    }
    
    /**
     * Test type casting for typed array with a class
     */
    public function testToTypedArrayWithClass()
    {
        $this->assertNull(TypeCast::value(null)->toArray(FooBar::class));
        
        $this->assertSame([], TypeCast::value([])->toArray(FooBar::class));
        $this->assertSame([], TypeCast::value('')->toArray(FooBar::class));
        
        // Array
        $castArr = TypeCast::value([1, 20, 300])->toArray(FooBar::class);
        $this->assertInternalType('array', $castArr);
        $this->assertEquals([0, 1, 2], array_keys($castArr));
        
        $this->assertInstanceOf(FooBar::class, $castArr[0]);
        $this->assertInstanceOf(FooBar::class, $castArr[1]);
        $this->assertInstanceOf(FooBar::class, $castArr[2]);
        
        $this->assertSame(1, $castArr[0]->x);
        $this->assertSame(20, $castArr[1]->x);
        $this->assertSame(300, $castArr[2]->x);

        // value object
        $obj = (object)['red' => 1, 'green' => 20, 'blue' => 300];
        $castObj = TypeCast::value($obj)->toArray(FooBar::class);
        $this->assertInternalType('array', $castObj);
        $this->assertEquals(['red', 'green', 'blue'], array_keys($castObj));

        $this->assertInstanceOf(FooBar::class, $castObj['red']);
        $this->assertInstanceOf(FooBar::class, $castObj['green']);
        $this->assertInstanceOf(FooBar::class, $castObj['blue']);
        
        $this->assertSame(1, $castObj['red']->x);
        $this->assertSame(20, $castObj['green']->x);
        $this->assertSame(300, $castObj['blue']->x);
        
        // Scalar
        $castInt = TypeCast::value(20)->toArray(FooBar::class);
        $this->assertInternalType('array', $castInt);
        $this->assertEquals([0], array_keys($castInt));
        
        $this->assertInstanceOf(FooBar::class, $castInt[0]);
        $this->assertSame(20, $castInt[0]->x);
    }
    
    /**
     * Test type casting to an array using a callback for subtype
     */
    public function testToTypedArrayWithCallback()
    {
        $fn = function ($val) {
            return $val + 1;
        };
        
        $result = TypeCast::value([1, 4, 5])->toArray($fn);
        $this->assertSame([2, 5, 6], $result);
        
        $resultSingle = TypeCast::value(9)->toArray($fn);
        $this->assertSame([10], $resultSingle);
    }
    
    /**
     * Test type casting a string to an array of integers
     *
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to a integer
     */
    public function testToTypedArrayWithStringInt()
    {
        TypeCast::value('foo')->toArray('int');
    }

    
    /**
     * Test type casting presenting multiple types
     */
    public function testToMultiType()
    {
        $this->assertSame(10, TypeCast::value(10)->toMultiType(['int', 'boolean']));
        $this->assertSame(true, TypeCast::value(true)->toMultiType(['int', 'boolean']));
    }
    
    /**
     * Test type casting presenting multiple types
     */
    public function testToMultiTypeNull()
    {
        $this->assertSame(10, TypeCast::value('10')->toMultiType(['int', 'null']));
    }
    
    /**
     * Test type casting presenting multiple types with no matching type
     * 
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast string "foo" to integer|boolean
     */
    public function testToMultiTypeNoMatch()
    {
        TypeCast::value('foo')->toMultiType(['int', 'boolean']);
    }
    
    /**
     * Get type casting presenting multiple types
     */
    public function testToMultiTypeArray()
    {
        $this->assertSame([true, true, false], TypeCast::value([1, 'on', false])->toMultiType(['int', 'bool[]']));
    }
    
    /**
     * Test type casting presenting multiple types
     */
    public function testToMultiTypeTypedArray()
    {
        $this->assertSame([1, true, false], TypeCast::value([1, true, false])->toMultiType(['int[]', 'bool[]']));
    }
    
    /**
     * Test type casting presenting multiple array types with no matching type
     * 
     * @expectedException         PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage  Unable to cast an array to integer[]|boolean[]
     */
    public function testToMultiTypeTypedArrayNoMatch()
    {
        TypeCast::value([1, 'on', false])->toMultiType(['int[]', 'bool[]']);
    }
    
    
    /**
     * Test `to` method
     */
    public function testTo()
    {
        $typecast = $this->getMock(TypeCast::class, ['toInteger', 'toArray', 'toClass', 'toMultiType'], ['123']);
        
        $typecast->expects($this->once())->id('toInteger')->method('toInteger');
        $typecast->expects($this->exactly(2))->id('toArray')->method('toArray')->after('toInteger')
            ->withConsecutive([null], ['integer']);
        $typecast->expects($this->once())->id('toClass')->method('toClass')->after('toArray')->with(FooBar::class);
        $typecast->expects($this->once())->id('toMultiType')->method('toMultiType')->after('toClass')
            ->with(['string', 'integer[]']);
        
        $typecast->to('int');
        $typecast->to('array');
        $typecast->to('int[]');
        $typecast->to(FooBar::class);
        $typecast->to('string|integer[]');
    }
}
