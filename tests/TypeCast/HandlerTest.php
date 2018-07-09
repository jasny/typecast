<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCast\TypeGuess;

/**
 * @covers \Jasny\TypeCast\Handler
 */
class HandlerTest extends TestCase
{
    use \Jasny\TestHelper;

    /**
     * Test 'onFailure' method
     */
    public function testOnFailure()
    {
        $handler = $this->createPartialMock(Handler::class, ['getType', 'cast']);
        $handler->onFailure('foo');

        $onError = $this->getPrivateProperty($handler, 'failure');
        $this->assertSame('foo', $onError);
    }

    /**
     * Test 'dontCast' method, in case when error is a string
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to cast string "some_value" to some-type
     */
    public function testDontCast()
    {
        $handler = $this->createPartialMock(Handler::class, ['getType', 'cast']);
        $handler->expects($this->once())->method('getType')->willReturn('some-type');
        $handler->onFailure('RuntimeException');

        $this->callPrivateMethod($handler, 'dontCast', ['some_value']);
    }

    public function getPrivateProperty($obj, $prop) {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
