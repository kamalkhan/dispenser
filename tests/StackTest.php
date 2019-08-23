<?php

namespace Bhittani\Dispenser;

use SplStack;

class StackTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(DispenserInterface::class, new Stack);
    }

    /** @test */
    function it_extends_the_spl_stack()
    {
        $this->assertInstanceOf(SplStack::class, new Stack);
    }

    /** @test */
    function it_uses_a_stack()
    {
        $stack = new Stack;

        $stack->push(new Dispenser(function ($a, $b) { return $a . $b . 1; }));
        $stack->push(new Dispenser(function ($a, $b) { return $a . $b . 2; }));
        $stack->push(new Dispenser(function ($a, $b) { return $a . $b . 3; }));

        $this->assertEquals(['ab3', 'ab2', 'ab1'], $stack->dispense('a', 'b'));
    }
}
