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
     * @var boolean
     */
    private $ignoreItemsWithoutConstraints = false;

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
        $pathString = new KeyPath();
        $violations = new ConstraintViolationList();
        $matchedPathStrings = [];

        $this->internalValidate($array, $constraints, $pathString, $violations, $matchedPathStrings);

        // Find all unmatched constraints
        foreach ($constraints as $pathString => $constraint) {
            if(!isset($matchedPathStrings[$pathString])) {
                $violation = new ConstraintViolation('Missing array item.', '', [], '', $pathString, null);
                $violations->add($violation);
            }
        }

        return $violations;
    }

    private function internalValidate(array &$array, $constraints, KeyPath $keyPath,
        ConstraintViolationListInterface $violations, array &$matchedPathStrings)
    {
        foreach ($array as $key => &$item) {

            $keyPath->push($key);
            $pathString = $keyPath->getPathString();

            if(is_array($item)) {
                $this->internalValidate($item, $constraints, $keyPath, $violations, $matchedPathStrings);
                
                $keyPath->pop();
                continue;
            }

            if($this->ignoreItemsWithoutConstraints && !isset($constraints[$pathString])) {
                $keyPath->pop();
                continue;
            }

            if(!isset($constraints[$pathString])) {
                $violation = new ConstraintViolation('Unexpected array item.', '', [], '', $pathString, null);
                $violations->add($violation);
                $keyPath->pop();
                continue;
            }

            $keyConstraints = $constraints[$pathString];
            $originalViolations = $this->validator->validate($item, $keyConstraints);
            $violations->addAll($this->getViolationsForKey($originalViolations, $pathString));

            $matchedPathStrings[$pathString] = true;

            $keyPath->pop();
        }
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param string $keyPathString
     *
     * @return ConstraintViolationListInterface
     */
    private function getViolationsForKey($violations, $keyPathString)
    {
        $violationsForKey = new ConstraintViolationList();

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $newViolation = new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getRoot(),
                $keyPathString,
                $violation->getInvalidValue(),
                $violation->getPlural(),
                $violation->getCode(),
                $violation->getConstraint(),
                $violation->getCause()
            );

            $violationsForKey->add($newViolation);
        }

        return $violationsForKey;
    }

    /**
     * @return boolean
     */
    public function ignoreItemsWithoutConstraints()
    {
        return $this->ignoreItemsWithoutConstraints;
    }

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setIgnoreItemsWithoutConstraints($value)
    {
        $this->ignoreItemsWithoutConstraints = $value;

        return $this;
    }
}
