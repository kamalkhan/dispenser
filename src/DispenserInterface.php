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

interface DispenserInterface
{
    /**
     * Dispense the callback.
     *
     * @param array $args
     *
     * @return mixed
     */
    public function dispense(array $args);
}
