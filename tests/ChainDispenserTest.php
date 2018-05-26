<?php

namespace Bhittani\Dispenser;

use PHPUnit\Framework\TestCase;

class ChainDispenserTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(
            DispenserInterface::class,
            new ChainDispenser(new QueueDispenser)
        );
    }

    /** @test */
    function it_accpets_a_queue()
    {
        $queue = new QueueDispenser;

        $queue->push(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        });

        $queue->push(new Dispenser(function ($request, $next) {
            return '2f' . $next($request) . '2l';
        }));

        $queue->push(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        });

        $chain = new ChainDispenser($queue, function () {
            return 0;
        });

        $this->assertEquals('1f2f3f03l2l1l', $chain->dispense([2]));
    }

    /** @test */
    function it_accpets_a_stack()
    {
        $stack = new StackDispenser;

        $stack->push(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        });

        $stack->push(function ($request, $next) {
            return '2f' . $next($request) . '2l';
        });

        $stack->push(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        });

        $chain = new ChainDispenser($stack, function () {
            return 0;
        });

        $this->assertEquals('3f2f1f01l2l3l', $chain->dispense([2]));
    }

    /** @test */
    function it_accpets_a_priority_heap()
    {
        $ranking = new PriorityDispenser;

        $ranking->insert(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        }, 2);

        $ranking->insert(function ($request, $next) {
            return '2f' . $next($request) . '2l';
        }, 3);

        $ranking->insert(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        }, 1);

        $chain = new ChainDispenser($ranking, function () {
            return 0;
        });

        $this->assertEquals('2f1f3f03l1l2l', $chain->dispense([2]));
    }

    /** @test */
    function it_can_be_broken()
    {
        $queue = new QueueDispenser;

        $queue->push(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        });

        $queue->push(function ($request, $next) {
            return '2f' . '2l';
        });

        $queue->push(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        });

        $chain = new ChainDispenser($queue, function () {
            return 0;
        });

        $this->assertEquals('1f2f2l1l', $chain->dispense([2]));
    }

    /** @test */
    function it_does_not_require_a_fallback()
    {
        $queue = new QueueDispenser;

        $queue->push(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        });

        $queue->push(function ($request, $next) {
            return '2f' . $next($request) . '2l';
        });

        $queue->push(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        });

        $chain = new ChainDispenser($queue);

        $this->assertEquals('1f2f3f3l2l1l', $chain->dispense([2]));
    }

    /** @test */
    function it_can_be_empty()
    {
        $queue = new QueueDispenser;

        $chain = new ChainDispenser($queue, function ($n) {
            return ++$n;
        });

        $this->assertEquals(3, $chain->dispense([2]));
    }

    /** @test */
    function it_can_be_empty_and_without_a_fallback()
    {
        $queue = new QueueDispenser;

        $chain = new ChainDispenser($queue);

        $this->assertNull($chain->dispense([2]));
    }
}
