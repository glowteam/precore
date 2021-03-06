<?php
/*
 * Copyright (c) 2012 Janos Szurovecz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace precore\lang;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

require_once 'Color.php';
require_once 'Animal.php';
require_once 'EmptyEnum.php';
require_once 'EmptyConstructorEnum.php';
require_once 'MissingConstructorArgs.php';

/**
 * Description of EnumTest
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EnumTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $red = Color::$RED;
        self::assertInstanceOf(Color::className(), $red);
        self::assertEquals('RED', $red->name());
        self::assertTrue($red->equals(Color::valueOf('RED')));
        self::assertEquals(2, count(Color::values()));
    }

    /**
     * @test
     */
    public function shouldCheckClassAndNameForEquals()
    {
        self::assertFalse(Color::$RED->equals(Color2::$RED));
    }

    public function testValues()
    {
        $colors = Color::values();
        $animals = Animal::values();
        self::assertContains(Color::$RED, $colors);
        self::assertContains(Animal::$CAT, $animals);
        self::assertEquals(2, count($colors));
        self::assertEquals(3, count($animals));
        self::assertEquals(0, count(EmptyEnum::values()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidValueOf()
    {
        Color::valueOf('invalid');
    }

    public function testConstructorCall()
    {
        self::assertEquals(Color::BLUE_HEX, Color::$BLUE->getHexCode());
        self::assertEquals(EmptyConstructorEnum::VALUE, EmptyConstructorEnum::$ITEM1->getValue());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConstructor()
    {
        MissingConstructorArgs::init();
    }

    public function testOrdinal()
    {
        self::assertEquals(0, Color::$RED->ordinal());
        self::assertEquals(1, Color::$BLUE->ordinal());
        self::assertGreaterThan(0, Color::$BLUE->compareTo(Color::$RED));
        self::assertLessThan(0, Color::$RED->compareTo(Color::$BLUE));

        self::assertEquals(0, Animal::$DOG->ordinal());
        self::assertEquals(1, Animal::$CAT->ordinal());
        self::assertEquals(2, Animal::$HORSE->ordinal());
    }

    /**
     * @test
     * @expectedException \precore\lang\ClassCastException
     */
    public function shouldFailIfNotTheSameInstancesAreCompared()
    {
        Color::$BLUE->compareTo(Animal::$DOG);
    }

    public function testSerialization()
    {
        $obj = Animal::$DOG;
        $ser = serialize($obj);
        $ret = unserialize($ser);
        self::assertEquals($obj->name(), $ret->name());
        self::assertEquals($obj->ordinal(), $ret->ordinal());
    }
}

class Color2 extends Enum
{
    public static $RED;
}
Color2::init();
