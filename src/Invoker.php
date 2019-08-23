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

trait Invoker
{
    protected function toInvokable($valueOrCallableOrDispenser)
    {
        if (is_callable($valueOrCallableOrDispenser)) {
            return $valueOrCallableOrDispenser;
        }

        if ($valueOrCallableOrDispenser instanceof DispenserInterface) {
            return [$valueOrCallableOrDispenser, 'dispense'];
        }

        return function () use ($valueOrCallableOrDispenser) {
            return $valueOrCallableOrDispenser;
        };
    }

    protected function invoke($valueOrCallableOrDispenser, array $parameters)
    {
        return call_user_func_array($this->toInvokable($valueOrCallableOrDispenser), $parameters);
    }
}
