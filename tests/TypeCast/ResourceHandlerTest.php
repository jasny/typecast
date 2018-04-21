<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\ResourceHandler;

/**
 * @covers Jasny\TypeCast\ResourceHandler
 * @covers Jasny\TypeCast\Handler
 */
class ResourceHandlerTest extends TestCase
{
    use \Jasny\TestHelper;
    
    /**
     * @var ResourceHandler
     */
    protected $handler;
    
    public function setUp()
    {
        $this->handler = new ResourceHandler();
    }
    
    public function testUsingTypecast()
    {
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $ret = $this->handler->usingTypecast($typecast);
        $this->assertSame($this->handler, $ret);
    }
    
    public function testForType()
    {
        $ret = $this->handler->forType('resource');
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
            [true, 'boolean'],
            [1, 'integer'],
            ['foo', 'string "foo"'],
            [['hello', 'world'], 'array'],
            [(object)['a' => 'z', 'b' => 'y'], 'stdClass object']
        ];
    }
    
    /**
     * @dataProvider castProvider
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testCast($value, $type)
    {
        $this->expectExceptionMessage("Unable to cast $type to resource");
        
        $this->handler->cast($value);
    }
    
    /**
     * Test type casting an resource to resource
     */
    public function testCastWithResource()
    {
        if (!function_exists('imagecreate')) {
            $this->markTestSkipped("GD not available. Using gd resource for test.");
        }
        
        $resource = imagecreate(10, 10);
        
        $ret = $this->handler->cast($resource);
        $this->assertSame($resource, $ret);
    }
    
}
