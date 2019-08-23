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

trait IsSpl
{
    use Invoker;

    /** {@inheritdoc} */
    public function dispense(...$parameters)
    {
        return array_values(array_map(function ($callableOrDispenser) use ($parameters) {
            return $this->invoke($callableOrDispenser, ...$parameters);
        }, iterator_to_array($this)));
    }
}
