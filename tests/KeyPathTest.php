<?php

namespace Aa\ArrayValidator\Tests;

use Aa\ArrayValidator\KeyPath;
use PHPUnit_Framework_TestCase;

class KeyPathTest extends PHPUnit_Framework_TestCase
{
    public function testPushAndPopGiveCorrectPathString()
    {
        $path = new KeyPath();
        
        $this->assertEquals('', $path->getPathString());
        
        $path->push('Hobbit');
        $this->assertEquals('Hobbit', $path->getPathString());
        
        $path->push('Orc');
        $this->assertEquals('Hobbit/Orc', $path->getPathString());

        $path->push('Man');
        $this->assertEquals('Hobbit/Orc/Man', $path->getPathString());

        $path->pop();
        $this->assertEquals('Hobbit/Orc', $path->getPathString());

        $path->push('Dwarf');
        $path->push('Ent');
        $this->assertEquals('Hobbit/Orc/Dwarf/Ent', $path->getPathString());

        $path->pop();
        $path->pop();
        $path->pop();
        $this->assertEquals('Hobbit', $path->getPathString());

        $path->pop();
        $this->assertEquals('', $path->getPathString());
    }

    public function testThatTooManyPopsDoesNotBreak()
    {
        $path = new KeyPath();

        $path->push('Hobbit');
        $path->push('Orc');
        $path->pop();
        $path->pop();
        $path->pop();

        $this->assertEquals('', $path->getPathString());
    }
}
