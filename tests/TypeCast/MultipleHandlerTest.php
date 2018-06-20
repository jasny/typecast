<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Jasny\TypeCast;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\MultipleHandler;
use Jasny\TypeCast\TypeGuessInterface;

/**
 * @covers \Jasny\TypeCast\MultipleHandler
 * @covers \Jasny\TypeCast\Handler
 */
class MultipleHandlerTest extends TestCase
{
    use \Jasny\TestHelper;

    public function testUsingTypecast()
    {
        $typeGuess = $this->createMock(TypeGuessInterface::class);
        $typecast = $this->createMock(TypeCastInterface::class);
        
        $handler = new MultipleHandler($typeGuess);
        $newHandler = $handler->usingTypecast($typecast);
        
        $this->assertNotSame($handler, $newHandler);
        $this->assertAttributeSame($typecast, 'typecast', $newHandler);
        
        $this->assertAttributeSame(null, 'typecast', $handler);
    }
    
    public function testForType()
    {
        $typeGuess = $this->createMock(TypeGuessInterface::class);

        $handler = new MultipleHandler($typeGuess);
        $newHandler = $handler->forType('string|integer[]');
        
        $this->assertInstanceOf(MultipleHandler::class, $newHandler);
        $this->assertNotSame($handler, $newHandler);
        $this->assertAttributeEquals(['string', 'integer[]'], 'types', $newHandler);
        
        $this->assertAttributeSame([], 'types', $handler);

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

    public function testCast()
    {
        $typeGuess = $this->createMock(TypeGuessInterface::class);
        $typecast = $this->createMock(TypeCast::class);

        $handler = (new MultipleHandler($typeGuess))->usingTypecast($typecast);
        $handler = $handler->forType('string|integer[]');

        $typeGuess->expects($this->once())->method('guessFor')->with(10)->willReturn('string');
        $typecast->expects($this->once())->method('forValue')->with(10)->willReturnSelf();
        $typecast->expects($this->once())->method('to')->with('string')->willReturn('10');

        $result = $handler->cast(10);
        $this->assertSame('10', $result);
    }

    public function shouldNotCastProvider()
    {
        return [
            ['hello', 'string|integer[]'],
            [[10, 20, 30], 'string|integer[]'],
            [null, 'string|integer[]'],
            ['hello', 'integer|mixed'],
            [['hello', 10], 'array|object'],
            [22.12, 'float'],
            [(object)['foo' => 'bar'], 'object|string'],
            [(object)['foo' => 'bar'], 'stdClass|string'],
            [[(object)['a' => 1], (object)['b' => 2]], 'stdClass[]|string']
        ];
    }

    /**
     * @dataProvider shouldNotCastProvider
     * @param mixed $value
     * @parma string $type
     */
    public function testShouldNotCast($value, $type)
    {
        $typeGuess = $this->createMock(TypeGuessInterface::class);
        $typecast = $this->createMock(TypeCast::class);

        $handler = (new MultipleHandler($typeGuess))->usingTypecast($typecast);
        $handler = $handler->forType($type);

        $typeGuess->expects($this->never())->method('guessFor');
        $typecast->expects($this->never())->method('forValue');
        $typecast->expects($this->never())->method('to');

        $result = $handler->cast($value);
        $this->assertSame($value, $result);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Type cast for multiple handler not set
     */
    public function testCastNotInitialized()
    {
        $typeGuess = $this->createMock(TypeGuessInterface::class);

        $handler = (new MultipleHandler($typeGuess))->forType('string');

        $handler->cast(10);
    }
}
