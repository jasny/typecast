<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCast\TypeGuess;

/**
 * @covers \Jasny\TypeCast\TypeGuess
 */
class TypeGuessTest extends TestCase
{
    use \Jasny\TestHelper;

    /**
     * @var TypeGuess
     */
    protected $typeGuess;

    public function setUp()
    {
        $this->typeGuess = new TypeGuess();
    }

    public function scalarProvider()
    {
        return [
            [1, [], null],
            [1, ['integer'], 'integer'],
            [1, ['integer', 'boolean'], 'integer'],
            [true, ['integer', 'boolean'], 'boolean'],
            ['on', ['string', 'boolean'], 'string'],
            ['foo', ['string', 'integer', 'float'], 'string'],
            ['10.0', ['integer', 'boolean'], 'integer'],
            ['1', ['integer', 'boolean'], 'integer'],
            ['on', ['integer', 'boolean'], 'boolean'],
            [10.0, ['integer', 'float'], 'float'],
            ['10.0', ['integer', 'float'], 'float'],
            ['10.0', ['string', 'integer', 'float'], 'float'],
            ['10', ['string', 'integer', 'float'], 'integer'],
            ['10', ['integer', 'null'], 'integer'],
            ['10', ['array', 'null'], 'array'],
            ['10', ['integer', 'array', 'stdClass'], 'integer'],
            ['2018-01-03', ['integer', 'DateTime', 'string'], 'DateTime'],
            ['hello', ['integer', 'string', 'Foo', 'Foo[]'], 'string'],
            ['hello', ['integer', 'Foo', 'Foo[]'], 'Foo'],
            ['hello', ['integer[]', 'boolean[]', 'object'], null]
        ];
    }

    /**
     * @dataProvider scalarProvider
     */
    public function testGuessForScalar($value, $types, $expected)
    {
        $type = $this->typeGuess->guess($value, $types);

        $this->assertEquals($expected, $type, sprintf('%s for %s', var_export($value, true), join('|', $types)));
    }

    public function scalarToArrayProvider()
    {
        return [
            ['10', ['string[]', 'array'], 'array'],
            [10, ['string[]', 'integer[]'], 'integer[]'],
            ['10', ['string[]', 'integer[]'], 'integer[]'],
            ['foo', ['string[]', 'integer[]'], 'string[]']
        ];
    }

    /**
     * @dataProvider scalarToArrayProvider
     */
    public function testGuessForScalarToArray($value, $types, $expected)
    {
        $type = $this->typeGuess->guess($value, $types);

        $this->assertEquals($expected, $type, sprintf('%s for %s', var_export($value, true), join('|', $types)));
    }

    public function arrayProvider()
    {
        return [
            [['foo', 'bar'], ['string', 'integer'], null],
            [[10, 20], ['string[]', 'integer[]'], 'integer[]'],
            [['10.0', 20], ['string[]', 'integer[]'], 'integer[]'],
            [[10, 20], ['float[]', 'integer[]'], 'integer[]'],
            [[10, 20], ['integer', 'integer[]'], 'integer[]'],
            [[10, 20], ['stdClass', 'integer[]'], 'integer[]'],
            [[10, 20], ['Foo', 'integer[]'], 'integer[]'],
            [[10, 20], ['DateTime[]', 'integer[]'], 'integer[]'],
            [[1525027695, 1525027635], ['DateTime[]', 'integer[]'], 'integer[]'],
            [['2018-01-03', 1525027635], ['DateTime[]', 'integer[]'], 'DateTime[]'],
            [['2018-01-03', '2018-01-04'], ['DateTime[]', 'string[]'], 'DateTime[]'],
            [['2018-01-03', 'hello'], ['DateTime[]', 'string[]'], 'string[]']
        ];
    }

    /**
     * @dataProvider arrayProvider
     */
    public function testGuessForArray($value, $types, $expected)
    {
        $type = $this->typeGuess->guess($value, $types);

        $this->assertEquals($expected, $type, sprintf('%s for %s', var_export($value, true), join('|', $types)));
    }

    /**
     * Provide data for testing 'guess' method for associative array value
     *
     * @return array
     */
    public function guessForAssocProvider()
    {
        return [
            [['foo' => 'bar'], ['string', 'integer'], null],
            [['foo' => 'bar'], ['string', 'integer', 'array'], 'array'],
            [['foo' => 'bar'], ['string', 'integer', 'object'], 'object'],
            [['foo' => 'bar'], ['string', 'integer', 'array', 'object'], null]
        ];
    }

    /**
     * @dataProvider guessForAssocProvider
     */
    public function testGuessForAssoc($value, $types, $expected)
    {
        $type = $this->typeGuess->guess($value, $types);

        $this->assertEquals($expected, $type, sprintf('%s for %s', var_export($value, true), join('|', $types)));
    }

    /**
     * Provide data for testing 'guess' method for object value
     *
     * @return array
     */
    public function guessForObjectProvider()
    {
        return [
            [(object)[], ['string', 'integer'], null],
            [(object)[], ['string', 'integer', 'array'], null],
            [(object)[], ['string', 'integer', 'object'], null],
            [(object)[], ['string', 'integer', 'stdClass'], 'stdClass'],
            [(object)[], ['string', 'integer', 'DateTime'], null],
            [(object)[], ['string', 'integer', 'Foo'], null],
            [(object)[], [\Iterator::class, 'integer'], 'Iterator|integer[]'],
        ];
    }

    /**
     * @dataProvider guessForObjectProvider
     */
    public function testGuessForObject($value, $types, $expected)
    {
        $type = $this->typeGuess->guess($value, $types);

        $this->assertEquals($expected, $type, sprintf('%s for %s', var_export($value, true), join('|', $types)));
    }

    /**
     * Provide data for testing 'guess' method for resource value
     *
     * @return array
     */
    public function guessForResourceProvider()
    {
        $resource = imagecreate(1, 1);

        return [
            [$resource, ['string', 'integer'], null],
            [$resource, ['string', 'integer', 'object'], null],
            [$resource, ['string', 'integer', 'resource'], 'resource'],
        ];
    }

    /**
     * @dataProvider guessForResourceProvider
     */
    public function testGuessForResourceObject($value, $types, $expected)
    {
        $type = $this->typeGuess->guess($value, $types);

        $this->assertEquals($expected, $type, sprintf('%s for %s', var_export($value, true), join('|', $types)));
    }
}
