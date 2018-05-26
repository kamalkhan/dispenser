<?php

namespace Bhittani\Dispenser;

use PHPUnit\Framework\TestCase;

class PipelineDispenserTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(
            DispenserInterface::class,
            new PipelineDispenser(new QueueDispenser)
        );
    }

    /** @test */
    function it_pipes_through_a_spl_queue_dispenser()
    {
        $queue = new QueueDispenser;

        $queue->push(new Dispenser(function ($a, $b) { return $a + $b; })); // 3
        $queue->push(new Dispenser(function ($n) { return $n - 5; })); // -2
        $queue->push(new Dispenser(function ($n) { return $n * 2; })); // -4

        $pipeline = new PipelineDispenser($queue);

        $this->assertEquals(-4, $pipeline->dispense([1, 2]));
    }

    /** @test */
    function it_pipes_through_a_spl_stack_dispenser()
    {
        $stack = new StackDispenser;

        $stack->push(new Dispenser(function ($n) { return $n - 5; })); // 1
        $stack->push(new Dispenser(function ($n) { return $n * 2; })); // 6
        $stack->push(new Dispenser(function ($a, $b) { return $a + $b; })); // 3

        $pipeline = new PipelineDispenser($stack);

        $this->assertEquals(1, $pipeline->dispense([1, 2]));
    }

    /** @test */
    function it_pipes_through_a_spl_heap_dispenser()
    {
        $rank = new PriorityDispenser;

        $rank->insert(new Dispenser(function ($n) { return $n * 2; }), 2); // 6
        $rank->insert(new Dispenser(function ($a, $b) { return $a + $b; }), 3); // 3
        $rank->insert(new Dispenser(function ($n) { return $n - 5; }), 1); // 1

        $pipeline = new PipelineDispenser($rank);

        $this->assertEquals(1, $pipeline->dispense([1, 2]));
    }
}
