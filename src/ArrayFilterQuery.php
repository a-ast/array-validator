<?php

namespace Aa\ArrayValidator;

class ArrayFilterQuery
{
    const REGEX_IDENTIFIER = '[_\-a-zA-Z0-9]+';
    const REGEX_IDENTIFIER_PATH = '[_/\-a-zA-Z0-9]+';
    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $query
     */
    function __construct($query)
    {
        $this->buildPattern($query);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function matches($path)
    {
        return preg_match($this->pattern, $path) === 1;
    }

    /**
     * @param $query
     */
    private function buildPattern($query)
    {
        $pattern = str_replace('*', self::REGEX_IDENTIFIER, $query);
        $pattern = str_replace('//', '/'.self::REGEX_IDENTIFIER_PATH.'/', $pattern);

        $this->pattern = '#^'.$pattern.'$#';
    }
}
