<?php

namespace Bhittani\Dispenser;

use PHPUnit\Framework\TestCase;

use SplQueue;

class QueueDispenserTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(DispenserInterface::class, new QueueDispenser);
    }

    /** @test */
    function it_extends_the_spl_queue()
    {
        $this->assertInstanceOf(SplQueue::class, new QueueDispenser);
    }

    /** @test */
    function it_uses_a_queue()
    {
        $queue = new QueueDispenser;

        $queue->push(new Dispenser(function ($a, $b) { return $a . $b . 1; }));
        $queue->push(new Dispenser(function ($a, $b) { return $a . $b . 2; }));
        $queue->push(function ($a, $b) { return $a . $b . 3; });

        $this->assertEquals(['ab1', 'ab2', 'ab3'], $queue->dispense(['a', 'b']));
    }
}
