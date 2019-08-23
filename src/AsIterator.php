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

use Iterator;

trait AsIterator
{
    use Invoker;

    /** {@inheritdoc} */
    public function dispense(...$parameters)
    {
        return array_map(function ($valueOrCallableOrDispenser) use ($parameters) {
            return $this->invoke($valueOrCallableOrDispenser, $parameters);
        }, $this->toArray());
    }

    public function toArray()
    {
        return $this->flatten(iterator_to_array($this));
    }

    protected function flatten($items)
    {
        if (! (is_array($items) || $items instanceof Iterator)) {
            return [$items];
        }

        if ($items instanceof Iterator) {
            $items = iterator_to_array($items);
        }

        return array_reduce($items, function ($carry, $item) {
            return array_merge($carry, $this->flatten($item));
        }, []);
    }
}
