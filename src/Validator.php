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
     * @var array
     */
    private $matchedConstraintQueries = [];

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

        $this->internalValidate($array, $constraints, $pathString, $violations);

        // Find all unmatched constraints
        foreach ($constraints as $constraintQuery => $c) {
            if(!isset($this->matchedConstraintQueries[$constraintQuery])) {
                $violation = new ConstraintViolation('Unused constraint.', '', [], '', $constraintQuery, null);
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
     */
    private function internalValidate(array &$array, $constraints, KeyPath $keyPath,
        ConstraintViolationListInterface $violations)
    {
         foreach ($array as $key => &$item) {

            $keyPath->push($key);

            $itemConstraints = $this->getItemConstraints($constraints, $keyPath);

            if(is_array($item)) {
                // Validate collection
                if(count($itemConstraints) > 0) {
                    $pathStringViolations = $this->validateItem($item, $keyPath, $itemConstraints);
                    $violations->addAll($pathStringViolations);
                }

                // Validate recursively collection items
                $this->internalValidate($item, $constraints, $keyPath, $violations);
                
                $keyPath->pop();
                continue;
            }

            if($this->ignoreItemsWithoutConstraints && count($itemConstraints) == 0) {
                $keyPath->pop();
                continue;
            }

            if(count($itemConstraints) == 0) {
                $violation = new ConstraintViolation('Unexpected item.', '', [], '', $keyPath->toString(), null);
                $violations->add($violation);
                $keyPath->pop();
                continue;
            }

            $pathStringViolations = $this->validateItem($item, $keyPath, $itemConstraints);
            $violations->addAll($pathStringViolations);

            $keyPath->pop();
        }
    }

    /**
     * @param mixed   $value
     * @param KeyPath $path
     * @param array   $constraints
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateItem($value, KeyPath $path, &$constraints)
    {
        $violations = $this->validator->validate($value, $constraints);
        return $this->getWrappedViolations($violations, $path->toString());
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param string $keyPathString
     *
     * @return ConstraintViolationListInterface
     */
    private function getWrappedViolations($violations, $keyPathString)
    {
        $wrappedViolations = new ConstraintViolationList();

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

            $wrappedViolations->add($newViolation);
        }

        return $wrappedViolations;
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

    /**
     * @param array   $constraints
     * @param KeyPath $keyPath
     *
     * @return array
     */
    private function getItemConstraints(array &$constraints, KeyPath $keyPath)
    {
        $filteredConstraints = [];

        foreach ($constraints as $constraintQuery => &$itemConstraints) {
            $arrayFilterQuery = new ArrayFilterQuery($constraintQuery);
            if($arrayFilterQuery->matches($keyPath->toString())) {
                $filteredConstraints = array_merge($filteredConstraints, $itemConstraints);

                $this->matchedConstraintQueries[$constraintQuery] = true;
            }
        }

        return $filteredConstraints;
    }
}
