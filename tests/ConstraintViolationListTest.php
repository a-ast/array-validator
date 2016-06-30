<?php

namespace Aa\ArrayValidator\Tests;

use Aa\ArrayValidator\ConstraintViolationList;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList as BaseConstraintViolationList;

class ConstraintViolationListTest extends PHPUnit_Framework_TestCase
{
    public function testCreatesEmptyList()
    {
        $list = new ConstraintViolationList();
        $this->assertEquals(0, $list->count());
    }

    public function testCreatesListUsingPropertyPath()
    {
        $violation = new ConstraintViolation('message', 'messageTemplate', [], 'root', 'oldPropertyPath', null);
        $baseList = new BaseConstraintViolationList([$violation]);

        $list = new ConstraintViolationList($baseList, 'propertyPath');
        $this->assertEquals(1, $list->count());
        $this->assertEquals('propertyPath', $list[0]->getPropertyPath());
    }
}
