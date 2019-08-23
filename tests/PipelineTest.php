<?php

namespace Bhittani\Dispenser;

class PipelineTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(DispenserInterface::class, new Pipeline);
    }

    /** @test */
    function it_dispenses_the_pipeline()
    {
        $pipeline = new Pipeline();

        $pipeline->push(new Dispenser(function ($n) { return $n / 5; })); // 10/5=2
        $pipeline->push(new Dispenser(function ($n) { return $n + 3; })); // 2+3=5
        $pipeline->push(function ($n) { return $n * 4; }); // 5*4=20

        $this->assertEquals(20, $pipeline->dispense(10));
    }

    /** @test */
    function it_pipes_through_a_queue_dispenser()
    {
        $queue = new Queue;

        $queue->push(new Dispenser(function ($n) { return $n / 5; })); // 10/5=2
        $queue->push(new Dispenser(function ($n) { return $n + 3; })); // 2+3=5
        $queue->push(function ($n) { return $n * 4; }); // 5*4=20

        $pipeline = new Pipeline;

        $pipeline->push($queue);

        $this->assertEquals(20, $pipeline->dispense(10));
    }

    /** @test */
    function it_pipes_through_a_stack_dispenser()
    {
        $stack = new Stack;

        $stack->push(new Dispenser(function ($n) { return $n / 5; })); // 100/5=20
        $stack->push(new Dispenser(function ($n) { return $n + 60; })); // 40+60=100
        $stack->push(function ($n) { return $n * 4; }); // 10*4=40

        $pipeline = new Pipeline;

        $pipeline->push($stack);

        $this->assertEquals(20, $pipeline->dispense(10));
    }

    /** @test */
    function it_pipes_through_a_priority_dispenser()
    {
        $priority = new Priority;

        $priority->insert(new Dispenser(function ($n) { return $n * 4; }), 1); // 5*4=20
        $priority->insert(new Dispenser(function ($n) { return $n / 5; }), 3); // 10/5=2
        $priority->insert(function ($n) { return $n + 3; }, 2); // 2+3=5

        $pipeline = new Pipeline();

        $pipeline->push($priority);

        $this->assertEquals(20, $pipeline->dispense(10));
    }

    /** @test */
    function it_pipes_through_mixed_dispensers()
    {
        $pipeline = new Pipeline();

        $pipeline->push(new Dispenser(function ($n) { return $n / 5; }));

        $pipeline->push($queue = new Queue);

        $queue->push(new Dispenser(function ($n) { return $n / 5; }));
        $queue->push(new Dispenser(function ($n) { return $n + 3; }));
        $queue->push(function ($n) { return $n * 4; });

        $pipeline->push(new Dispenser(function ($n) { return $n + 3; }));

        $pipeline->push($stack = new Stack);

        $stack->push(new Dispenser(function ($n) { return $n / 5; }));
        $stack->push(new Dispenser(function ($n) { return $n + 60; }));
        $stack->push(function ($n) { return $n * 4; });

        $pipeline->push(function ($n) { return $n * 4; });

        $pipeline->push($priority = new Priority);

        $priority->insert(new Dispenser(function ($n) { return $n * 4; }), 1);
        $priority->insert(new Dispenser(function ($n) { return $n / 5; }), 3);
        $priority->insert(function ($n) { return $n + 3; }, 2);

        $this->assertEquals(
            ((((((((10/5/5)+3)*4)+3)*4)+60)/5*4/5)+3)*4,
            $pipeline->dispense(10)
        );
    }
}
