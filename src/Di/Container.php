<?php

namespace PhpPmd\Pmd\Di;

class Container extends AbstractContainer implements \ArrayAccess
{
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->injection($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->__resolvedEntries[$offset]);
        unset($this->__definitions[$offset]);
    }

    function __set($name, $value)
    {
        $this->__resolvedEntries[$name] = $value;
    }

    function __get($name)
    {
        return $this->__resolvedEntries[$name];
    }

    function __isset($name)
    {
        return isset($this->__resolvedEntries[$name]);
    }

    function __unset($name)
    {
        if (isset($this->__resolvedEntries[$name])) {
            unset($this->__resolvedEntries[$name]);
            unset($this->__definitions[$name]);
        }
    }
}