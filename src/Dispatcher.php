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

use ArrayObject;
use IteratorAggregate;

class Dispatcher implements DispenserInterface, IteratorAggregate
{
    use AsIterator {
        toArray as iteratorToArray;
    }

    protected $subscribers = [];

    /** {@inheritdoc} */
    public function getIterator()
    {
        return new ArrayObject($this->subscribers);
    }

    /** {@inheritdoc} */
    public function dispense(...$parameters)
    {
        $key = array_shift($parameters);

        return array_map(function ($valueOrCallableOrDispenser) use ($parameters) {
            return $this->invoke($valueOrCallableOrDispenser, $parameters);
        }, $this->toArray($key));
    }

    public function subscribe($key, $subscriber)
    {
        if (! isset($this->subscribers[$key])) {
            $this->subscribers[$key] = [];
        }

        $this->subscribers[$key][] = $subscriber;

        return $this;
    }

    public function toArray($key = null)
    {
        if (! $key) {
            return $this->iteratorToArray();
        }

        if (! isset($this->subscribers[$key])) {
            return [];
        }

        return $this->flatten($this->subscribers[$key]);
    }
}
