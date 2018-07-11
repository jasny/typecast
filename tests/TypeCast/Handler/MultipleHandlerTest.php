<?php

namespace Jasny\TypeCast\Test\Handler;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Jasny\TestHelper;
use Jasny\TypeCast;
use Jasny\TypeCastInterface;
use Jasny\TypeCast\Handler\MultipleHandler;
use Jasny\TypeCast\Handler\StringHandler;
use Jasny\TypeCast\TypeGuessInterface;

/**
 * @covers \Jasny\TypeCast\Handler
 * @covers \Jasny\TypeCast\Handler\MultipleHandler
 */
class MultipleHandlerTest extends TestCase
{
    use TestHelper;

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

        $stringHandler = $this->createPartialMock(StringHandler::class, ['cast']);

        $typeGuess->expects($this->once())->method('guess')->with(10, ['string', 'integer[]'])->willReturn('string');
        $typecast->expects($this->once())->method('to')->with('string')->willReturn($stringHandler);
        $stringHandler->expects($this->once())->method('cast')->with(10)->willReturn('10');

        $result = $handler->cast(10);
        $this->assertSame('10', $result);
    }

    /**
     * Test that dontCast method is called
     */
    public function testDontCast()
    {
        $value = (object)[];
        $type = 'string|integer';

        $typeGuess = $this->createPartialMock(TypeGuessInterface::class, ['guess']);
        $typecast = $this->createMock(TypeCast::class);

        $handler = $this->createPartialMock(MultipleHandler::class, ['dontCast']);
        $this->setPrivateProperty($handler, 'typeGuess', $typeGuess);
        $this->setPrivateProperty($handler, 'typecast', $typecast);
        $handler = $handler->forType($type);

        $typeGuess->expects($this->once())->method('guess')->with($value, ['string', 'integer'])->willReturn(null);
        $handler->expects($this->once())->method('dontCast')->with($value)->willReturn($value);

        $result = $handler->cast($value);
        $this->assertSame($value, $result);
    }

    /**
     * Test 'getType' method
     */
    public function testGetType()
    {
        $handler = $this->createPartialMock(MultipleHandler::class, []);
        $handler = $handler->forTypes(['integer', 'string']);

        $result = $this->callPrivateMethod($handler, 'getType', []);

        $this->assertSame('integer|string', $result);
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
