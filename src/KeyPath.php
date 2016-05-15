<?php

namespace Aa\ArrayValidator;

class KeyPath
{
    /**
     * @var array
     */
    private $items = [];

    public function getPathString()
    {
        return implode('/', $this->items);    
    }

    /**
     * @param string $key
     */
    public function push($key)
    {
        array_push($this->items, $key);    
    }

    public function pop()
    {
        array_pop($this->items);
    }
}
