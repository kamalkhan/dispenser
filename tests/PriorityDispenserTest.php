<?php

namespace Bhittani\Dispenser;

use SplPriorityQueue;
use PHPUnit\Framework\TestCase;

class PriorityDispenserTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(DispenserInterface::class, new PriorityDispenser);
    }

    /** @test */
    function it_extends_the_spl_priority_queue()
    {
        $this->assertInstanceOf(SplPriorityQueue::class, new PriorityDispenser);
    }

    /** @test */
    function it_uses_a_priority()
    {
        $priority = new PriorityDispenser;

        $priority->insert(new Dispenser(function ($a, $b) { return $a . $b . 1; }), 2);
        $priority->insert(new Dispenser(function ($a, $b) { return $a . $b . 2; }), 3);
        $priority->insert(new Dispenser(function ($a, $b) { return $a . $b . 3; }), 1);

        $this->assertEquals(['ab2', 'ab1', 'ab3'], $priority->dispense(['a', 'b']));
    }
}
