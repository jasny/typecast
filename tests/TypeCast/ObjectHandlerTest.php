<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\ObjectHandler;
use Jasny\TypeCast\Test\Foo;
use Jasny\TypeCast\Test\FooBar;

/**
 * @covers Jasny\TypeCast\ObjectHandler
 * @covers Jasny\TypeCast\Handler
 */
class ObjectHandlerTest extends TestCase
{
    use \Jasny\TestHelper;
    
    /**
     * @var ObjectHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->handler = new ObjectHandler();
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $ret = $this->handler->usingTypecast($typecast);
        $this->assertSame($this->handler, $ret);
    }
    
    public function testForTypeNoClass()
    {
        $ret = $this->handler->forType('object');
        
        $this->assertSame($this->handler, $ret);
        $this->assertAttributeSame(null, 'class', $this->handler);
    }
    
    public function testForTypeClass()
    {
        $newHandler = $this->handler->forType('Foo');
        
        $this->assertInstanceOf(ObjectHandler::class, $newHandler);
        $this->assertNotSame($this->handler, $newHandler);
        $this->assertAttributeEquals('Foo', 'class', $newHandler);
        
        $this->assertAttributeSame(null, 'class', $this->handler);
        
        return $newHandler;
    }
    
    /**
     * @depends testForTypeClass
     */
    public function testForTypeWithoutClass($handler)
    {
        $newHandler = $handler->forType('object');
        
        $this->assertInstanceOf(ObjectHandler::class, $newHandler);
        $this->assertNotSame($handler, $newHandler);
        $this->assertAttributeSame(null, 'class', $newHandler);
        
        $this->assertAttributeEquals('Foo', 'class', $handler);
    }
    
    /**
     * @depends testForTypeClass
     */
    public function testForTypeSameClass($handler)
    {
        $ret = $handler->forType('Foo');
        
        $this->assertSame($handler, $ret);
        $this->assertAttributeEquals('Foo', 'class', $handler);
    }
    
    public function testCastNull()
    {
        $this->assertNull($this->handler->cast(null));
    }
    
    public function testCastNop()
    {
        $obj = (object)['red' => 1, 'green' => 20, 'blue' => 300];
        $actual = $this->handler->cast($obj);
        
        $this->assertInternalType('object', $actual);
        $this->assertSame($obj, $actual);
    }
    
    public function testCastAssoc()
    {
        $assoc = ['red' => 1, 'green' => 20, 'blue' => 300];
        $actual = $this->handler->cast($assoc);
        
        $this->assertInternalType('object', $actual);
        $this->assertEquals((object)$assoc, $actual);
    }
    
    /**
     * @expectedException         \PHPUnit\Framework\Error\Notice
     * @expectedExceptionMessage  Unable to cast string "foo" to object
     */
    public function testToObjectWithScalar()
    {
        $this->handler->cast('foo');
    }
    
    /**
     * @expectedException         \PHPUnit\Framework\Error\Notice
     * @expectedExceptionMessage  Unable to cast gd resource to object
     */
    public function testToObjectWithResource()
    {
        if (!function_exists('imagecreate')) {
            $this->markTestSkipped("GD not available. Using gd resource for test.");
        }
        
        $resource = imagecreate(10, 10);
        $this->handler->cast($resource);
    }
    
    
    public function testCastToDateTime()
    {
        $this->assertNull($this->handler->forType(\DateTime::class)->cast(null));

        $date = $this->handler->forType(\DateTime::class)->cast('2014-06-01T01:15:00+00:00');
        $this->assertInstanceOf(\DateTime::class, $date);
        $this->assertSame('2014-06-01T01:15:00+00:00', $date->format('c'));
    }

    public function testCastToArrayObject()
    {
        $this->assertNull($this->handler->forType(\ArrayObject::class)->cast(null));

        $arrayish = $this->handler->forType(\ArrayObject::class)->cast(['x' => 10, 'y' => 12]);
        $this->assertInstanceOf(\ArrayObject::class, $arrayish);
        $this->assertSame(['x' => 10, 'y' => 12], $arrayish->getArrayCopy());
    }
    
    public function testCastToClassWithNull()
    {
        $this->assertNull($this->handler->forType(FooBar::class)->cast(null));
    }
    
    public function testCastToClassNonExistingWithNull()
    {
        $this->assertNull($this->handler->forType('Jasny\TypeCast\Test\NonExisting')->cast(null));
    }
    
    public function testCastNopToClass()
    {
        $foobar = new FooBar('abc123');
        $this->assertSame($foobar, $this->handler->forType(FooBar::class)->cast($foobar));
    }
    
    public function testCastToClassWithInt()
    {
        $castedInt = $this->handler->forType(FooBar::class)->cast(22);
        
        $this->assertInstanceOf(FooBar::class, $castedInt);
        $this->assertAttributeSame(22, 'x', $castedInt);
    }
    
    public function testCastToClassWithIntSetState()
    {
        $castedInt = $this->handler->forType(Foo::class)->cast(22);
        
        $this->assertInstanceOf(Foo::class, $castedInt);
        $this->assertAttributeSame(22, 'data', $castedInt);
    }


    public function assocProvider()
    {
        return [
            [['x' => 10, 'y' => 12]],
            [(object)['x' => 10, 'y' => 12]]
        ];
    }
    
    /**
     * @dataProvider assocProvider
     */
    public function testCastToClassWithArray($value)
    {
        $actual = $this->handler->forType(FooBar::class)->cast($value);
        
        $this->assertInstanceOf(FooBar::class, $actual);
        $this->assertSame(10, $actual->x);
        $this->assertSame(12, $actual->y);
    }
    
    public function testCastToStdClassNop()
    {
        $object = (object)['foo' => 'bar'];
        $this->assertSame($object, $this->handler->forType(\stdClass::class)->cast($object));
    }
    
    public function testCastToStdClass()
    {
        $object = (object)['foo' => 'bar'];
        $this->assertEquals($object, $this->handler->forType(\stdClass::class)->cast(['foo' => 'bar']));
    }
    
    public function testCastToStdClassWithObject()
    {
        $foobar = new FooBar();
        $foobar->x = 'abc123';
        $foobar->y = 123;
        
        $expected = (object)['x' => 'abc123', 'y' => 123];
        $actual = $this->handler->forType(\stdClass::class)->cast($foobar);
        
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @expectedException         \PHPUnit\Framework\Error\Notice
     * @expectedExceptionMessage  Unable to cast integer to Jasny\TypeCast\Test\NonExisting object: Class doesn't exist
     */
    public function testCastToClassNonExisting()
    {
        $this->handler->forType('Jasny\TypeCast\Test\NonExisting')->cast(22);
    }
}
