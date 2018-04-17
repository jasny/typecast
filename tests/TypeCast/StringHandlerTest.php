<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\StringHandler;
use Jasny\TypeCast\Test\FooBar;

/**
 * @covers Jasny\TypeCast\StringHandler
 * @covers Jasny\TypeCast\Handler
 */
class StringHandlerTest extends TestCase
{
    use \Jasny\TestHelper;
    
    /**
     * @var StringHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->handler = new StringHandler();
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $ret = $this->handler->usingTypecast($typecast);
        $this->assertSame($this->handler, $ret);
    }
    
    public function testForType()
    {
        $ret = $this->handler->forType('string');
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
            ['foo', 'foo'],
            ['100', '100'],
            ['', ''],
            ['1', 1],
            ['1', true],
            ['', false]
        ];
    }
    
    /**
     * @dataProvider castProvider
     */
    public function testCast($expected, $value)
    {
        $this->assertSame($expected, $this->handler->cast($value));
    }
    
    public function testToStringWithStringable()
    {
        $foobar = new FooBar();  // Implements __toString
        $this->assertSame('foo', $this->handler->cast($foobar));
    }
    
    public function testToStringWithDateTime()
    {
        $date = new \DateTime("2014-12-31 23:15 UTC");
        $this->assertSame('2014-12-31T23:15:00+00:00', $this->handler->cast($date));
    }
    
    /**
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast array to string
     */
    public function testToStringWithArray()
    {
        $this->handler->cast([10, 20]);
    }
    
    /**
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast stdClass object to string
     */
    public function testToStringWithObject()
    {
        $this->handler->cast((object)['foo' => 'bar']);
    }
    
    /**
     * @expectedException         PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage  Unable to cast gd resource to string
     */
    public function testToStringWithResource()
    {
        if (!function_exists('imagecreate')) {
            $this->markTestSkipped("GD not available. Using gd resource for test.");
        }
        
        $resource = imagecreate(10, 10);
        $this->handler->cast($resource);
    }
}
