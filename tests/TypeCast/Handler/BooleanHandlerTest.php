<?php

namespace Jasny\TypeCast\Test\Handler;

use PHPUnit\Framework\TestCase;
use Jasny\TestHelper;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\Handler\BooleanHandler;

/**
 * @covers \Jasny\TypeCast\Handler
 * @covers \Jasny\TypeCast\Handler\BooleanHandler
 */
class BooleanHandlerTest extends TestCase
{
    use TestHelper;

    /**
     * @var BooleanHandler
     */
    protected $handler;

    public function setUp()
    {
        $this->handler = new BooleanHandler();
    }

    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);

        $ret = $this->handler->usingTypecast($typecast);
        $this->assertSame($this->handler, $ret);
    }

    public function testForType()
    {
        $ret = $this->handler->forType('boolean');
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
            [true, true],
            [true, 1],
            [true, -1],
            [true, 10],
            [true, '1'],
            [true, 'true'],
            [true, 'yes'],
            [true, 'on'],
            [false, false],
            [false, 0],
            [false, '0'],
            [false, 'false'],
            [false, 'no'],
            [false, 'off']
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
     * @expectedException         \PHPUnit\Framework\Error\Notice
     * @expectedExceptionMessage  Unable to cast string "foo" to boolean
     */
    public function testCastWithRandomString()
    {
        $this->handler->cast('foo');
    }

    /**
     * @expectedException         \PHPUnit\Framework\Error\Notice
     * @expectedExceptionMessage  Unable to cast array to boolean
     */
    public function testCastWithArray()
    {
        $this->handler->cast([10, 20]);
    }

    /**
     * @expectedException         \PHPUnit\Framework\Error\Notice
     * @expectedExceptionMessage  Unable to cast stdClass object to boolean
     */
    public function testCastWithObject()
    {
        $this->handler->cast((object)['foo' => 'bar']);
    }

    /**
     * Test type casting an resource to boolean
     *
     * @expectedException         \PHPUnit\Framework\Error\Notice
     * @expectedExceptionMessage  Unable to cast gd resource to boolean
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
