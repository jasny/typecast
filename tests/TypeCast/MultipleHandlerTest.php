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

}
