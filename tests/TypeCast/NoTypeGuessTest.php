<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCast\NoTypeGuess;

/**
 * @covers \Jasny\TypeCast\NoTypeGuess
 */
class NoTypeGuessTest extends TestCase
{
    use \Jasny\TestHelper;

    /**
     * Provide data for testing 'guess' method
     *
     * @return array
     */
    public function guessProvider()
    {
        return [
            ['foo', ['int', 'string'], 'string'],
            [true, ['int', 'string'], null],
            [(object)[], ['int', 'string', 'stdClass'], 'stdClass'],
            [(object)[], ['int', 'string'], null]
        ];
    }

    /**
     * Test 'guess' method
     *
     * @dataProvider guessProvider
     */
    public function testGuess($value, array $types, $expected)
    {
        $noTypeGuess = $this->createPartialMock(NoTypeGuess::class, []);
        $result = $noTypeGuess->guess($value, $types);

        $this->assertSame($expected, $result);
    }
}
