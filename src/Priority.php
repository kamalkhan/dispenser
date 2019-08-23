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

use SplPriorityQueue;

class Priority extends SplPriorityQueue implements DispenserInterface
{
    use AsIterator;

    /** {@inheritdoc} */
    public function compare($priority1, $priority2)
    {
        if ($priority1 == $priority2) {
            return 0;
        }

        return $priority1 >= $priority2 ? 1 : -1;
    }
}
