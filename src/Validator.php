<?php

namespace Aa\ArrayValidator;

use Symfony\Component\Validator\Constraint;
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
     * Validate array.
     *
     * @param array $array
     * @param array $constraints
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

    /**
     * @param array                            $array
     * @param array                            $constraints
     * @param KeyPath                          $keyPath
     * @param ConstraintViolationListInterface $violations
     * @param array                            $matchedPathStrings
     */
    private function internalValidate(array &$array, $constraints, KeyPath $keyPath,
        ConstraintViolationListInterface $violations, array &$matchedPathStrings)
    {
        foreach ($array as $key => &$item) {

            $keyPath->push($key);
            $pathString = $keyPath->toString();

            if(is_array($item)) {
                // Validate collection
                if(isset($constraints[$pathString])) {
                    $pathStringViolations = $this->validateByPathString($item, $pathString, $constraints[$pathString]);
                    $violations->addAll($pathStringViolations);
                    $matchedPathStrings[$pathString] = true;
                }

                // Validate recursively collection items
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

            $pathStringViolations = $this->validateByPathString($item, $pathString, $constraints[$pathString]);
            $violations->addAll($pathStringViolations);
            $matchedPathStrings[$pathString] = true;

            $keyPath->pop();
        }
    }

    /**
     * @param mixed $value
     * @param string $pathString
     * @param Constraint[] $constraints
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateByPathString($value, $pathString, $constraints)
    {
        $violations = $this->validator->validate($value, $constraints);

        return $this->getWrappedViolations($violations, $pathString);
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param string $keyPathString
     *
     * @return ConstraintViolationListInterface
     */
    private function getWrappedViolations($violations, $keyPathString)
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
