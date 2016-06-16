<?php

namespace Aa\ArrayValidator;

class KeyPath
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @return string
     */
    public function toString()
    {
        return implode('/', $this->items);    
    }

    /**
     * Push new item.
     *
     * @param string $key
     */
    public function push($key)
    {
        array_push($this->items, $key);    
    }

    /**
     * Pop new item.
     */
    public function pop()
    {
        array_pop($this->items);
    }
}
