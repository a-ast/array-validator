<?php

namespace Aa\ArrayValidator\Tests;

use Aa\ArrayValidator\ConstraintReader;
use Aa\ArrayValidator\Validator;
use Aa\ArrayValidator\YamlFixtureAwareTrait;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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

        $this->assertEquals($this->getViolationsAsArray($violations), $violationsData);
    }

    public function dataProvider()
    {
        return $this->getDataFromFixtureFile('fixtures', __DIR__.'/fixtures');
    }

    private function getViolationsAsArray(ConstraintViolationListInterface $violations)
    {
        $result = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $result[] = [
                'key_path' => $violation->getPropertyPath(),
                'invalid_value' => $violation->getInvalidValue(),
                'message' => $violation->getMessage(),
            ];
        }

        return $result;
    }
}
