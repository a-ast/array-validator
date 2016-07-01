<?php

namespace Aa\ArrayValidator;

use Aa\ArrayValidator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    /**
     * Symfony validator.
     *
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
        $keyPath = new KeyPath();
        $violations = new ConstraintViolationList();

        $this->internalValidate($array, $constraints, $keyPath, $violations, false);

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
        ConstraintViolationListInterface $violations, $ignoreMissingConstraintForCollections)
    {
         foreach ($array as $key => &$item) {

            $keyPath->push($key);

            $itemConstraints = $this->getItemConstraints($constraints, $keyPath);

            if(is_array($item)) {
                // Validate collection
                if(count($itemConstraints) > 0) {
                    $itemViolations = $this->validateItem($item, $keyPath, $itemConstraints);
                    $violations->addAll($itemViolations);
                }

                // Validate recursively collection items
                $this->internalValidate($item, $constraints, $keyPath, $violations, count($itemConstraints) > 0);
                
                $keyPath->pop();
                continue;
            }

            if($this->ignoreItemsWithoutConstraints && count($itemConstraints) == 0) {
                $keyPath->pop();
                continue;
            }

            if(count($itemConstraints) == 0) {
                if(!$ignoreMissingConstraintForCollections) {
                    $violation = new ConstraintViolation('Unexpected item.', '', [], '', $keyPath->toString(), null);
                    $violations->add($violation);
                }
                $keyPath->pop();
                continue;
            }

            $itemViolations = $this->validateItem($item, $keyPath, $itemConstraints);
            $violations->addAll($itemViolations);

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
        return new ConstraintViolationList($violations, $path->toString());
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
