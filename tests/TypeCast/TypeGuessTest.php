<?php

namespace Jasny\TypeCast;

use PHPUnit\Framework\TestCase;
use Jasny\TypeCast\TypeGuess;

/**
 * @covers Jasny\TypeCast\TypeGuess
 */
class TypeGuessTest extends TestCase
{
    use \Jasny\TestHelper;

    /**
     * @var TypeGuess
     */
    protected $typeguess;
    
    public function setUp()
    {
        $this->typeguess = new TypeGuess();
    }
    
    public function testForTypes()
    {
        $new = $this->typeguess->forTypes(['string', 'integer[]']);
        
        $this->assertInstanceOf(TypeGuess::class, $new);
        $this->assertNotSame($this->typeguess, $new);
        $this->assertAttributeEquals(['string', 'integer[]'], 'types', $new);
        
        $this->assertAttributeSame([], 'types', $this->typeguess);
        
        return $new;
    }
    
    /**
     * @depends testForType
     */
    public function testForTypeSameSubtype($typeguess)
    {
        $ret = $typeguess->forType(['integer[]', 'string']);
        
        $this->assertSame($typeguess, $ret);
        $this->assertAttributeEquals(['string', 'integer[]'], 'types', $ret);
    }
    
    public function scalarProvider()
    {
        return [
            [1, ['integer', 'boolean'], ['integer']],
            [true, ['integer', 'boolean'], ['boolean']],
            ['on', ['string', 'boolean'], ['boolean']],
            ['foo', ['string', 'integer', 'float'], ['string']],
            ['10.0', ['integer', 'boolean'], ['integer']],
            ['1', ['integer', 'boolean'], ['integer']],
            ['on', ['integer', 'boolean'], ['boolean']],
            ['10.0', ['integer', 'float'], ['float']],
            ['10.0', ['string', 'integer', 'float'], ['float']],
            ['10', ['string', 'integer', 'float'], ['integer']],
            ['10', ['integer', 'null'], ['integer']],
            ['10', ['array', 'null'], ['array']],
            ['10', ['integer', 'array', 'stdClass'], ['integer']],
            ['2018-01-03', ['integer', 'DateTime', 'string'], ['DateTime']],
            ['hello', ['integer', 'Foo', 'Foo[]'], ['Foo']]
        ];
    }
    
    /**
     * @dataProvider scalarProvider
     */
    public function testGuessForScalar($value, $types, $expected)
    {
        $type = $this->typeguess->forTypes($types)->guessFor($value);
        
        $this->assertEquals($expected, $type);
    }
    
    public function castNopArrayProvider()
    {
        return [
            [['foo', 'bar'], 'string[]|integer[]'],
            [[10, 20], 'string[]|integer[]'],
            [[10, 20], 'integer|integer[]'],
            [[10, 20], 'stdClass|integer[]'],
            [[10, 20], 'Foo|integer[]'],
            [[10, 20], 'DateTime[]|integer[]']
        ];
    }
    
    /**
     * @dataProvider castNopArrayProvider
     */
    public function testCastNopArray($value, $type)
    {
        $this->typecast->expects($this->never())->method('forValue');
        $this->typecast->expects($this->never())->method('to');
        
        $actual = $this->typeguess->forType($type)->cast($value);
        $this->assertSame($value, $actual);
    }
    
    public function testCastArray()
    {
    }
}
