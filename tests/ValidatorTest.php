<?php

namespace Aa\ArrayValidator\Tests;

use Aa\ArrayValidator\ConstraintReader;
use Aa\ArrayValidator\Validator;
use PHPUnit_Framework_TestCase;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    use YamlFixtureAwareTrait;

    /**
     * @dataProvider dataProvider
     *
     * @param array $array
     * @param array $constraintsData
     * @param array $violationsData
     */
    public function testValidate(array $array, array $constraintsData, array $violationsData)
    {
        $reader = new ConstraintReader();
        $constraints = $reader->read($constraintsData);

        $validator = new Validator();
        $violations = $validator->validate($array, $constraints);
    }

    public function dataProvider()
    {
        return $this->getDataFromFixtureFile('fixtures');
    }
}
