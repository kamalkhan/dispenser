<?php

namespace Bhittani\Dispenser;

use PHPUnit\Framework\TestCase;

class EventDispenserTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(
            DispenserInterface::class,
            new EventDispenser
        );
    }

    /** @test */
    function it_publishes_subscribers_by_key()
    {
        $emitter = new EventDispenser;

        $emitter->subscribe('foo', new Dispenser(function ($a, $b) {
            return 'foo' . $a . $b;
        }));

        $emitter->subscribe('bar', function ($a, $b) {
            return 'bar' . $a . $b;
        });

        $this->assertEquals(['fooab'], $emitter->dispense(['foo', 'a', 'b']));
        $this->assertEquals(['barab'], $emitter->dispense(['bar', 'a', 'b']));
    }
}
