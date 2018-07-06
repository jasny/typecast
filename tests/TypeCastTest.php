<?php

namespace Jasny;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCast;
use Jasny\TypeCast\Handler;
use Jasny\TypeCast\HandlerInterface;

/**
 * @covers \Jasny\TypeCast
 */
class TypeCastTest extends TestCase
{
    public function handlerProvider()
    {
        return [
            ['array', Handler\ArrayHandler::class],
            ['boolean', Handler\BooleanHandler::class],
            ['float', Handler\NumberHandler::class],
            ['integer', Handler\NumberHandler::class],
            ['integer|float', Handler\NumberHandler::class],
            ['mixed', Handler\MixedHandler::class],
            ['object', Handler\ObjectHandler::class],
            ['resource', Handler\ResourceHandler::class],
            ['string', Handler\StringHandler::class],
            ['multiple', Handler\MultipleHandler::class],
            ['integer[]', Handler\ArrayHandler::class],
            ['Foo', Handler\ObjectHandler::class],
            ['Foo|bar', Handler\MultipleHandler::class]
        ];
    }
    
    /**
     * @dataProvider handlerProvider
     * 
     * @param string $type
     * @param string $class
     */
    public function testTo($type, $class)
    {
        $typecast = new TypeCast();
        
        $handler = $typecast->to($type);

        $this->assertInstanceOf($class, $handler);
    }

    /**
     * @expectedException OutOfBoundsException
     * @expectedExceptionMessage Unable to find handler to cast to 'cow'
     */
    public function testGetHandlerUnknown()
    {
        $typecast = new TypeCast([]);
        $typecast->to('cow');
    }

    public function aliasProvider()
    {
        return [
            ['foo', 'integer', 'integer'],
            ['foo[]', 'integer[]', 'array'],
            ['foo|boolean', 'integer|boolean', 'multiple']
        ];
    }
    
    /**
     * @dataProvider aliasProvider
     * 
     * @param string $type
     * @param string $normalType
     * @param string $expectHandler
     */
    public function testAlias($type, $normalType, $expectHandler)
    {
        $handlers = [
            'integer' => $this->createMock(HandlerInterface::class),
            'array' => $this->createMock(HandlerInterface::class),
            'multiple' => $this->createMock(HandlerInterface::class)
        ];

        foreach ($handlers as $hdl) {
            $hdl->expects($this->once())->method('usingTypecast')->willReturnSelf();
        }

        $typecast = (new TypeCast($handlers))->alias('foo', 'integer');

        $handler = $handlers[$expectHandler];
        $handler->expects($this->once())->method('forType')->with($normalType)->willReturnSelf();

        $actual = $typecast->to($type);

        $this->assertSame($handler, $actual);
    }
}
