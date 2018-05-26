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
    use Process;

    /**
     * {@inheritdoc}
     */
    public function dispense(array $args)
    {
        $response = [];

        $this->rewind();

        while ($this->valid()) {
            $response[] = $this->process($this->current(), $args);
            $this->next();
        }

        return $response;
    }
}
