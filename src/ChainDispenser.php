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

class ChainDispenser extends PipelineDispenser
{
    protected $fallback;

    public function __construct(IteratorInterface $iterator, $fallback = null)
    {
        parent::__construct($iterator);

        $this->fallback = $fallback ?: new Dispenser(function () {});
    }

    /**
     * {@inheritdoc}
     */
    public function dispense(array $args)
    {
        $chain = [$this, 'next'];

        foreach ($this->iterator as $dispenser) {
            $chain = $this->push($chain, $dispenser);
        }

        return $this->process($chain, $args, $this->fallback);
    }

    protected function push($chain, $dispenser)
    {
        return new Dispenser(function () use ($chain, $dispenser) {
            list($next, $args) = $this->explode(func_get_args());

            $next = new Dispenser(function ($args) use ($dispenser, $next) {
                return $this->process($dispenser, $args, $next);
            });

            return $this->process($chain, $args, $next);
        });
    }

    protected function next()
    {
        list($chain, $args) = $this->explode(func_get_args());

        return $this->process($chain, $args);
    }

    protected function process($dispenser, $args, $next = null)
    {
        return parent::process($dispenser, $this->sanitizeArgs($args, $next));
        // return parent::process($dispenser, $next ? $this->sanitizeArgs($args, $next) : $args);
    }

    protected function explode(array $args)
    {
        return [array_pop($args), $args];
    }

    // Single item array becomes first citizen.
    // ['foo'], 'bar', ['a', ['b']]
    // ['foo', 'bar', ['a', '[b]]]
    protected function sanitizeArgs()
    {
        $args = func_get_args();

        array_walk($args, function (&$arg) {
            if (is_array($arg) && (count($arg) == 1)) {
                $arg = array_shift($arg);
            }
        });

        return $args;
    }
}
