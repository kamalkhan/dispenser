<?php

namespace Bhittani\Dispenser;

use PHPUnit\Framework\TestCase;

class DelegatorDispenserTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(DispenserInterface::class, new DelegatorDispenser);
    }

    /** @test */
    function it_delegates_an_action_to_an_aggregate_handler()
    {
        $aggregate1 = new AggregateDispenser;
        $aggregate1->add('foo', new Dispenser(function ($str) {
            return strtolower($str);
        }));

        $aggregate2 = new AggregateDispenser;
        $aggregate2->add('bar', new Dispenser(function ($str) {
            return strtoupper($str);
        }));

        $delegator = new DelegatorDispenser;
        $delegator->delegate($aggregate1);
        $delegator->delegate($aggregate2);

        $this->assertEquals('bar', $delegator->dispense(['foo', 'BaR']));
    }
}
