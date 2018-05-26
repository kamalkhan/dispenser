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

use Countable;
use ArrayAccess;
use ArrayObject;

class AggregateDispenser implements AggregateDispenserInterface, Countable, ArrayAccess
{
    /**
     * Dispensers.
     *
     * @var array[DispenserInterface]
     */
    protected $dispensers = [];

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return isset($this->dispensers[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->dispensers[$key];
        }

        throw NotFoundException::key($key);
    }

    /**
     * Clear the dispensers.
     *
     * @return AggregateInterface
     */
    public function clear()
    {
        $this->dispensers = [];

        return $this;
    }

    /**
     * Get all of the dispensers.
     *
     * @return array[DispenserInterface]
     */
    public function all()
    {
        return $this->dispensers;
    }

    /**
     * Add a dispenser.
     *
     * @param string             $key
     * @param DispenserInterface $dispenser
     *
     * @return AggregateInterface
     */
    public function add($key, DispenserInterface $dispenser)
    {
        $this->dispensers[$key] = $dispenser;

        return $this;
    }

    /**
     * Add a dispenser.
     *
     * @param string $key
     *
     * @return AggregateInterface
     */
    public function remove($key)
    {
        unset($this->dispensers[$key]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayObject($this->dispensers);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->dispensers);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }
}
