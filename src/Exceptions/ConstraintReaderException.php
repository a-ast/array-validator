<?php

namespace Aa\ArrayValidator\Exceptions;

use Exception;
use RuntimeException;

class ConstraintReaderException extends RuntimeException
{
    /**
     * @var string
     */
    private $keyPath;
    /**
     * @var int
     */
    private $index;

    public function __construct($keyPath, $index, $code = 0, Exception $previous = null)
    {
        $this->keyPath = $keyPath;
        $this->index = $index;
        $message = sprintf('Syntax error in the constraint for key \'%s\' in line %d', $keyPath, $index);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getKeyPath()
    {
        return $this->keyPath;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }
}
