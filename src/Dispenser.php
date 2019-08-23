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

class Dispenser implements DispenserInterface
{
    /**
     * Dispenser callback.
     *
     * @var callable
     */
    protected $callback;

    /**
     * Creates the dispenser.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(...$parameters)
    {
        return $this->dispense(...$parameters);
    }

    /** {@inheritdoc} */
    public function dispense(...$parameters)
    {
        return call_user_func_array($this->callback, $parameters);
    }
}
