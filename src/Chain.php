<?php

/*
 * This file is part of bhittani/dispenser.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace Bhittani\Dispenser;

use ArrayIterator;
use IteratorAggregate;

class Chain implements DispenserInterface, IteratorAggregate
{
    use AsIterator;

    protected $fallback;
    protected $items = [];

    public function __construct($fallback = null)
    {
        $this->fallback = $fallback ?: function () {};
    }

    /** {@inheritdoc} */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /** {@inheritdoc} */
    public function dispense(...$parameters)
    {
        $chain = function (...$parameters) {
            $chain = array_pop($parameters);

            return $this->invoke($chain, $parameters);
        };

        foreach ($this->toArray() as $dispenser) {
            $chain = $this->chain($chain, $dispenser);
        }

        return $this->invoke($chain, array_merge($parameters, [$this->fallback]));
    }

    public function push($item)
    {
        $this->items[] = $item;
    }

    protected function chain($chain, $dispenser)
    {
        return function (...$parameters) use ($chain, $dispenser) {
            $next = array_pop($parameters);

            $next = function (...$parameters) use ($dispenser, $next) {
                return $this->invoke($dispenser, array_merge($parameters, [$next]));
            };

            return $this->invoke($chain, array_merge($parameters, [$next]));
        };
    }
}
