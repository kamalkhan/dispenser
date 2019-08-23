<?php

namespace Bhittani\Dispenser;

use SplPriorityQueue;

class PriorityTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(DispenserInterface::class, new Priority);
    }

    /** @test */
    function it_extends_the_spl_priority_queue()
    {
        $this->assertInstanceOf(SplPriorityQueue::class, new Priority);
    }

    /** @test */
    function it_dispenses_the_heap()
    {
        $priority = new Priority;

        $priority->insert(new Dispenser(function ($a, $b) { return $a . $b . 3; }), 3);
        $priority->insert(new Dispenser(function ($a, $b) { return $a . $b . 1; }), 1);
        $priority->insert(function ($a, $b) { return $a . $b . 2; }, 2);

        $this->assertEquals(['ab1', 'ab2', 'ab3'], $priority->dispense('a', 'b'));
    }
}
