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

class Pipeline extends Queue
{
    /** {@inheritdoc} */
    public function dispense(...$parameters)
    {
        // $response = array_reduce($this->toArray(), function ($response, $callableOrDispenser) {
        //     return [$this->invoke($callableOrDispenser, $response)];
        // }, $parameters);

        // return count($response) > 1 ? $response : (reset($response) ?: null);

        foreach ($this->toArray() as $dispenser) {
            $parameters = [$response = $this->invoke($dispenser, $parameters)];
        }

        return $response;
    }
}
