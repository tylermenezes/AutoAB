<?php

namespace AutoAB\Internal;


/**
 * 
 * 
 * @author      Tyler Menezes <tylermenezes@gmail.com>
 * @copyright   Copyright (c) Tyler Menezes. Released under the Perl Artistic License 2.0.
 *
 * @package AutoAB\Internal
 */
class Running implements \ArrayAccess, \Iterator {

    private $index;
    public function current()
    {
        global $ab_enrolled_tests;
        $keys = array_keys($ab_enrolled_tests);
        return $ab_enrolled_tests[$keys[$this->index]];
    }

    public function key()
    {
        global $ab_enrolled_tests;
        $keys = array_keys($ab_enrolled_tests);
        return $keys[$this->index];
    }

    public function next()
    {
        $this->index++;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid()
    {
        global $ab_enrolled_tests;
        return $this->index < count(array_keys($ab_enrolled_tests));
    }

    public function offsetExists($key)
    {
        global $ab_enrolled_tests;
        return array_key_exists($key, $ab_enrolled_tests);
    }

    public function offsetGet($key)
    {
        global $ab_enrolled_tests;
        return $ab_enrolled_tests[$key];
    }

    public function offsetSet($key, $val)
    {
        throw new \InvalidArgumentException("Cannot set in the AB tests array.");
    }

    public function offsetUnset($key)
    {
        throw new \InvalidArgumentException("Cannot unset in the AB tests array.");
    }

    public function __toString()
    {
        global $ab_enrolled_tests;
        return json_encode((object)$ab_enrolled_tests);
    }
}
