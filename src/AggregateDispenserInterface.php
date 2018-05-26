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

interface AggregateDispenserInterface
{
    /**
     * Tells whether the key is recognizable.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Get a dispenser by key.
     *
     * @param string $key
     *
     * @throws NotFoundException if the key is not recognizable
     *
     * @return DispenserInterface
     */
    public function get($key);
}
