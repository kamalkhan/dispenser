<?php

namespace Bhittani\Dispenser;

use PHPUnit\Framework\TestCase;

class DispenserTest extends TestCase
{
    function foo($a, $b)
    {
        return $a . $b;
    }

    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $dispenser = new Dispenser(function () {});

        $this->assertInstanceOf(DispenserInterface::class, $dispenser);
    }

    /** @test */
    function it_accepts_a_closure()
    {
        $dispenser = new Dispenser(function ($a, $b) {
            return $a . $b;
        });

        $this->assertEquals('ab', $dispenser->dispense(['a', 'b']));
    }

    /** @test */
    function it_accepts_a_callable()
    {
        $dispenser = new Dispenser([$this, 'foo']);

        $this->assertEquals('ab', $dispenser->dispense(['a', 'b']));
    }

    /** @test */
    function it_accepts_an_invokable()
    {
        $dispenser = new Dispenser(new FooDispenser);

        $this->assertEquals('ab', $dispenser->dispense(['a', 'b']));
    }
}

class FooDispenser
{
    public function __invoke($a, $b)
    {
        return $a . $b;
    }
}
