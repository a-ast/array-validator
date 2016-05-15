<?php

namespace Aa\ArrayValidator;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator = null)
    {
        $this->validator = $validator ? : Validation::createValidator();
    }

    /**
     * Validate array
     *
     * @param array $array
     * @param       $constraints
     *
     * @return ConstraintViolationListInterface
     */
    public function validate(&$array, $constraints)
    {
        $keyPath = new KeyPath();
        $violations = new ConstraintViolationList();

        $this->internalValidate($array, $constraints, $keyPath, $violations);

        return $violations;
    }

    private function internalValidate(&$array, $constraints, KeyPath $keyPath,
        ConstraintViolationListInterface $violations)
    {
        foreach ($array as $key => &$item) {

            $keyPath->push($key);

            if(is_array($item)) {
                $this->internalValidate($item, $constraints, $keyPath, $violations);
                
                $keyPath->pop();
                continue;
            }

            if(!isset($constraints[$keyPath->getPathString()])) {

                $violation = new ConstraintViolation('Unexpected array item', '', [], '', $keyPath->getPathString(), null);
                $violations->add($violation);

                $keyPath->pop();
                continue;
            }

            $keyConstraints = $constraints[$keyPath->getPathString()];

            $originalViolations = $this->validator->validate($item, $keyConstraints);
            /** @var ConstraintViolation $violation */
            foreach ($originalViolations as $violation) {
                $newViolation = new ConstraintViolation(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getRoot(),
                    $keyPath->getPathString(),
                    $violation->getInvalidValue(),
                    $violation->getPlural(),
                    $violation->getCode(),
                    $violation->getConstraint(),
                    $violation->getCause()
                );

                $violations->add($newViolation);
            }

            $keyPath->pop();
        }
    }
}
