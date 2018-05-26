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

trait Process
{
    protected function toProcess($dispenserOrCallable)
    {
        if ($dispenserOrCallable instanceof DispenserInterface) {
            return [$dispenserOrCallable, 'dispense'];
        }

        return $dispenserOrCallable;
    }

    protected function process($dispenserOrCallable, $args)
    {
        if ($dispenserOrCallable instanceof DispenserInterface) {
            return call_user_func($this->toProcess($dispenserOrCallable), $args);
        }

        return call_user_func_array($this->toProcess($dispenserOrCallable), $args);
    }
}
