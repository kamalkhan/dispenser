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

class EventDispenser implements DispenserInterface
{
    use Process;

    protected $subscribers = [];

    public function subscribe($key, $subscriber)
    {
        if (! isset($this->subscribers[$key])) {
            $this->subscribers[$key] = [];
        }

        $this->subscribers[$key][] = $subscriber;

        return $this;
    }

    public function dispense(array $args)
    {
        $key = array_shift($args);

        $responses = [];

        if (! isset($this->subscribers[$key])) {
            return $responses;
        }

        foreach ($this->subscribers[$key] as $subscriber) {
            $responses[] = $this->process($subscriber, $args);
        }

        return $responses;
    }
}
