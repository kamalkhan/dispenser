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

class DelegatorDispenser implements DispenserInterface
{
    /**
     * Delegations.
     *
     * @var array[AggregateInterface]
     */
    protected $delegates = [];

    /**
     * {@inheritdoc}
     */
    public function dispense(array $args)
    {
        $key = array_shift($args);

        foreach ($this->delegates as $delegate) {
            if ($delegate->has($key)) {
                return $delegate->get($key)->dispense($args);
            }
        }

        throw NotFoundException::key($key);
    }

    /**
     * Add a delgation.
     *
     * @param AggregateInterface $aggregate
     */
    public function delegate(AggregateDispenserInterface $aggregate)
    {
        $this->delegates[] = $aggregate;
    }
}
