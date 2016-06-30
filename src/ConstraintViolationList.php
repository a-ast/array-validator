<?php

namespace Aa\ArrayValidator;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList as BaseConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationList extends BaseConstraintViolationList
{
    /**
     * Constructor.
     *
     * @param ConstraintViolationListInterface|array $violations
     * @param string $propertyPath
     */
    public function __construct(ConstraintViolationListInterface $violations = null, $propertyPath = '')
    {
        if(null === $violations) {
            return parent::__construct([]);
        }

        foreach ($violations as $violation) {
            $newViolation = new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getRoot(),
                $propertyPath,
                $violation->getInvalidValue(),
                $violation->getPlural(),
                $violation->getCode(),
                $violation->getConstraint(),
                $violation->getCause()
            );

            $this->add($newViolation);
        }
    }
}
