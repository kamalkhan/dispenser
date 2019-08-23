<?php

namespace Bhittani\Dispenser;

class ChainTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(DispenserInterface::class, new Chain);
    }

    /** @test */
    function it_dispenses_the_chain()
    {
        $chain = new Chain;

        $chain->push(function ($foo, $bar, $next) {
            return '(' . $next($foo, $bar) . ')';
        });

        $chain->push(function ($foo, $bar, $next) {
            return $foo.$next($foo, $bar);
        });

        $chain->push(function ($foo, $bar, $next) {
            return $bar.$next($foo, $bar);
        });

        $this->assertEquals('(middleware)', $chain->dispense('middle', 'ware'));
    }

    /** @test */
    function it_accepts_a_fallback()
    {
        $chain = new Chain(function ($foo, $bar) {
            return $foo.$bar;
        });

        $chain->push(function ($foo, $bar, $next) {
            return '(' . $next($foo, $bar) . ')';
        });

        $this->assertEquals('(middleware)', $chain->dispense('middle', 'ware'));
    }

    /** @test */
    function it_chains_a_queue_dispenser()
    {
        $queue = new Queue;

        $queue->push(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        });

        $queue->push(function ($request, $next) {
            return '2f' . $next($request) . '2l';
        });

        $queue->push(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        });

        $chain = new Chain(function ($request) {
            return $request;
        });

        $chain->push($queue);

        $this->assertEquals('1f2f3f!3l2l1l', $chain->dispense('!'));
    }

    /** @test */
    function it_chains_a_stack_dispenser()
    {
        $stack = new Stack;

        $stack->push(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        });

        $stack->push(function ($request, $next) {
            return '2f' . $next($request) . '2l';
        });

        $stack->push(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        });

        $chain = new Chain(function ($request) {
            return $request;
        });

        $chain->push($stack);

        $this->assertEquals('3f2f1f!1l2l3l', $chain->dispense('!'));
    }

    /** @test */
    function it_chains_a_priority_dispenser()
    {
        $priority = new Priority;

        $priority->insert(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        }, 2);

        $priority->insert(function ($request, $next) {
            return '2f' . $next($request) . '2l';
        }, 3);

        $priority->insert(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        }, 1);

        $chain = new Chain(function ($request) {
            return $request;
        });

        $chain->push($priority);

        $this->assertEquals('2f1f3f!3l1l2l', $chain->dispense('!'));
    }

    /** @test */
    function it_accepts_a_mixture_of_dispensers()
    {
        $chain = new Chain(function ($request) {
            return $request;
        });

        $chain->push($queue = new Queue);

        $queue->push(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        });

        $chain->push(function ($request, $next) {
            return '0f' . $next($request) . '0l';
        });

        $queue->push(function ($request, $next) {
            return '2f' . $next($request) . '2l';
        });

        $queue->push(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        });

        $this->assertEquals('1f2f3f0f!0l3l2l1l', $chain->dispense('!'));
    }

    /** @test */
    function it_can_be_broken()
    {
        $queue = new Queue;

        $queue->push(function ($request, $next) {
            return '1f' . $next($request) . '1l';
        });

        $queue->push(function ($request, $next) {
            return '2f' . '2l';
        });

        $queue->push(function ($request, $next) {
            return '3f' . $next($request) . '3l';
        });

        $chain = new Chain(function ($request) {
            return '!';
        });

        $chain->push($queue);

        $this->assertEquals('1f2f2l1l', $chain->dispense('!'));
    }

    /** @test */
    function it_can_be_empty()
    {
        $queue = new Queue;

        $chain = new Chain(function ($n) {
            return $n + 1;
        });

        $chain->push($queue);

        $this->assertEquals(3, $chain->dispense(2));
    }

    /** @test */
    function it_can_be_empty_and_without_a_fallback()
    {
        $queue = new Queue;

        $chain = new Chain;

        $chain->push($queue);

        $this->assertNull($chain->dispense('!'));
    }

    /** @test */
    function it_can_be_empty_and_without_any_dispensers()
    {
        $chain = new Chain;

        $this->assertNull($chain->dispense('!'));
    }
}
