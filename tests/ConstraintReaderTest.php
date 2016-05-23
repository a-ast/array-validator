<?php

namespace Aa\ArrayValidator\Tests;

use Aa\ArrayValidator\ConstraintReader;
use PHPUnit_Framework_TestCase;

class ConstraintReaderTest extends PHPUnit_Framework_TestCase
{
    public function testRead()
    {
        $reader = new ConstraintReader();

        $data = [
            'name' => [
                'NotBlank',
                'Type(type="string", message="Name must be string")',
                'Choice(choices={"Bilbo", "Frodo"})',
            ],
            'url'  => [
                'NotNull',
                'Url(protocols = {"http", "https"}, checkDNS = true)',
            ],
        ];

        $constraints = $reader->read($data);

        $this->assertCount(2, $constraints);
        $this->assertCount(3, $constraints['name']);
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\NotBlank', $constraints['name'][0]);
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\Type', $constraints['name'][1]);
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\Choice', $constraints['name'][2]);
        $this->assertCount(2, $constraints['url']);
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\NotNull', $constraints['url'][0]);
        $this->assertInstanceOf('Symfony\Component\Validator\Constraints\Url', $constraints['url'][1]);
    }

    /**
     * @expectedException \Aa\ArrayValidator\Exceptions\ConstraintReaderException
     * @expectedExceptionMessage Syntax error in the constraint for key 'name' in line 1
     */
    public function testReadFailsIfConstraintDefinitionHasSyntaxError()
    {
        $reader = new ConstraintReader();

        $data = [
            'name' => [
                'NotBlank',
                'Choice(choices={"Bilbo", "Frodo")',
            ]
        ];

        $reader->read($data);
    }
}
