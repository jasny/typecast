<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TestHelper;
use Jasny\TypeCast\typeGuess;

/**
 * @covers \Jasny\TypeCast\typeGuess
 */
class typeGuessTest extends TestCase
{
    use TestHelper;

    /**
     * @var typeGuess
     */
    protected $typeGuess;
    
    public function setUp()
    {
        $this->typeGuess = new typeGuess();
    }
    
    public function testForTypes()
    {
        $new = $this->typeGuess->forTypes(['string', 'integer[]']);
        
        $this->assertInstanceOf(typeGuess::class, $new);
        $this->assertNotSame($this->typeGuess, $new);
        $this->assertAttributeEquals(['string', 'integer[]'], 'types', $new);
        
        $this->assertAttributeSame([], 'types', $this->typeGuess);
        
        return $new;
    }
    
    /**
     * @depends testForTypes
     */
    public function testForTypeSameSubtype(typeGuess $typeGuess)
    {
        $ret = $typeGuess->forTypes(['integer[]', 'string']);
        
        $this->assertSame($typeGuess, $ret);
        $this->assertAttributeEquals(['string', 'integer[]'], 'types', $ret);
    }
    
    public function scalarProvider()
    {
        return [
            [1, ['integer', 'boolean'], 'integer'],
            [true, ['integer', 'boolean'], 'boolean'],
            ['on', ['string', 'boolean'], 'boolean'],
            ['foo', ['string', 'integer', 'float'], 'string'],
            ['10.0', ['integer', 'boolean'], 'integer'],
            ['1', ['integer', 'boolean'], 'integer'],
            ['on', ['integer', 'boolean'], 'boolean'],
            ['10.0', ['integer', 'float'], 'float'],
            ['10.0', ['string', 'integer', 'float'], 'float'],
            ['10', ['string', 'integer', 'float'], 'integer'],
            ['10', ['integer', 'null'], 'integer'],
            ['10', ['array', 'null'], 'array'],
            ['10', ['integer', 'array', 'stdClass'], 'integer'],
            ['2018-01-03', ['integer', 'DateTime', 'string'], 'DateTime'],
            ['hello', ['integer', 'string', 'Foo', 'Foo[]'], 'string'],
            ['hello', ['integer', 'Foo', 'Foo[]'], 'Foo']
        ];
    }
    
    /**
     * @dataProvider scalarProvider
     */
    public function testGuessForScalar($value, $types, $expected)
    {
        $type = $this->typeGuess->forTypes($types)->guessFor($value);
        
        $this->assertEquals($expected, $type, sprintf('%s for %s', var_export($value, true), join('|', $types)));
    }

    public function arrayProvider()
    {
        return [
            [['foo', 'bar'], ['string[]', 'integer[]'], 'string[]'],
            [[10, 20], ['string[]', 'integer[]'], 'integer[]'],
            [['10.0', 20], ['string[]', 'integer[]'], 'integer[]'],
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
        $type = $this->typeGuess->forTypes($types)->guessFor($value);

        $this->assertEquals($expected, $type, sprintf('%s for %s', var_export($value, true), join('|', $types)));
    }
}
