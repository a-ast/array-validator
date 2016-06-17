<?php

namespace Aa\ArrayValidator\Tests;

use Aa\ArrayValidator\ArrayFilterQuery;
use Aa\ArrayValidator\YamlFixtureAwareTrait;
use PHPUnit_Framework_TestCase;

class ArrayFilterQueryTest extends PHPUnit_Framework_TestCase
{
    use YamlFixtureAwareTrait;

    /**
     * @dataProvider dataProvider
     */
    public function testMatches($query, $paths)
    {
//        $query = new ArrayFilterQuery('aa');
//        $this->assertEquals(true, $query->matches('aa'));
//        $this->assertEquals(false, $query->matches('bb'));

        $query = new ArrayFilterQuery($query);

        foreach ($paths as $path => $matches) {
            $this->assertEquals($matches, $query->matches($path));
        }

    }

    public function dataProvider()
    {
        return $this->getDataFromFixtureFile('array-query', __DIR__.'/fixtures');
    }
}
