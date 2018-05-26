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

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public static function key($key)
    {
        return new static(sprintf(
            'The [%s] key is not recognizable.',
            $key
        ));
    }
}
