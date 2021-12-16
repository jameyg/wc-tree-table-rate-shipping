<?php
namespace Trs\Mapping\Lazy\Wrappers;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;


class LazyArray implements IteratorAggregate, Countable, ArrayAccess
{
    public function __construct($loader, $count = null)
    {
        if (is_array($loader)) {
            $this->array = $loader;
        }
        else {
            $this->loader = $loader;
        }

        $this->count = $count;
    }

    public function getIterator()
    {
        $this->load();
        return new ArrayIterator($this->array);
    }

    public function offsetExists($offset)
    {
        $this->load();
        return array_key_exists($offset, $this->array);
    }

    public function offsetGet($offset)
    {
        $this->load();
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->load();
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->load();
        unset($this->array[$offset]);
    }

    public function count()
    {
        if (isset($this->count)) {
            return $this->count;
        }

        $this->load();
        return count($this->array);
    }


    private $loader;
    private $array;
    private $count;

    private function load()
    {
        if (!isset($this->array))
        {
            $this->array = (array)call_user_func($this->loader);
            unset($this->loader);
            unset($this->count);
        }
    }
}