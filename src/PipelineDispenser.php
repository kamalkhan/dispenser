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

class PipelineDispenser implements DispenserInterface
{
    use Process;

    /**
     * Iterator.
     *
     * @var IteratorInterface
     */
    protected $iterator;

    /**
     * Creates the pipeline.
     *
     * @param IteratorInterface $iterator
     */
    public function __construct(IteratorInterface $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function dispense(array $args)
    {
        foreach ($this->iterator as $dispenser) {
            $args = [$response = $this->process($dispenser, $args)];
        }

        return $response;
    }
}
