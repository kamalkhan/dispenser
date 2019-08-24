<?php

namespace Bhittani\Dispenser;

class DispatcherTest extends TestCase
{
    /** @test */
    function it_implements_the_dispenser_interface()
    {
        $this->assertInstanceOf(DispenserInterface::class, new Dispatcher);
    }

    /** @test */
    function it_dispatches_subscribers_based_on_a_publisher()
    {
        $dispatcher = new Dispatcher;

        $dispatcher->subscribe('foo', new Dispenser(function ($a, $b) {
            return $a.'1foo1'.$b;
        }));

        $dispatcher->subscribe('foo', new Dispenser(function ($a, $b) {
            return $a.'2foo2'.$b;
        }));

        $dispatcher->subscribe('bar', function ($a, $b) {
            return $a.'bar'.$b;
        });

        $this->assertEquals(['abarb'], $dispatcher->dispense('bar', 'a', 'b'));
        $this->assertEquals(['a1foo1b', 'a2foo2b'], $dispatcher->dispense('foo', 'a', 'b'));
    }

    /** @test */
    function it_is_silent_when_there_are_no_subscribers_for_a_publisher()
    {
        $dispatcher = new Dispatcher;

        $this->assertEquals([], $dispatcher->dispense('foo'));
    }
}
