<?php

namespace Aa\ArrayValidator\Tests;

use Aa\ArrayValidator\KeyPath;
use PHPUnit_Framework_TestCase;

class KeyPathTest extends PHPUnit_Framework_TestCase
{
    public function testPushAndPopGiveCorrectPathString()
    {
        $path = new KeyPath();
        
        $this->assertEquals('', $path->toString());
        
        $path->push('Hobbit');
        $this->assertEquals('Hobbit', $path->toString());
        
        $path->push('Orc');
        $this->assertEquals('Hobbit/Orc', $path->toString());

        $path->push('Man');
        $this->assertEquals('Hobbit/Orc/Man', $path->toString());

        $path->pop();
        $this->assertEquals('Hobbit/Orc', $path->toString());

        $path->push('Dwarf');
        $path->push('Ent');
        $this->assertEquals('Hobbit/Orc/Dwarf/Ent', $path->toString());

        $path->pop();
        $path->pop();
        $path->pop();
        $this->assertEquals('Hobbit', $path->toString());

        $path->pop();
        $this->assertEquals('', $path->toString());
    }

    public function testThatTooManyPopsDoesNotBreak()
    {
        $path = new KeyPath();

        $path->push('Hobbit');
        $path->push('Orc');
        $path->pop();
        $path->pop();
        $path->pop();

        $this->assertEquals('', $path->toString());
    }
}
