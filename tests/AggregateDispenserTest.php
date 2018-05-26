<?php

namespace Bhittani\Dispenser;

use PHPUnit\Framework\TestCase;

class AggregateDispenserTest extends TestCase
{
    var $aggregate;
    var $dispenser;

    function setUp()
    {
        $this->aggregate = new AggregateDispenser;
        $this->dispenser = new Dispenser(function () {});
    }

    /** @test */
    function it_implements_the_aggregate_interface()
    {
        $this->assertInstanceOf(AggregateDispenserInterface::class, $this->aggregate);
    }

    /** @test */
    function it_has_zero_dispensers()
    {
        $this->assertCount(0, $this->aggregate);
    }

    // AggregateInterface::add()
    /** @test */
    function it_registers_a_dispenser()
    {
        $this->aggregate->add('foo', $this->dispenser);

        $this->assertCount(1, $this->aggregate);

        $this->aggregate->add('bar', $this->dispenser);

        $this->assertCount(2, $this->aggregate);
    }

    // AggregateInterface::has()
    /** @test */
    function it_tells_whether_a_specific_dispenser_exists()
    {
        $this->aggregate->add('foo', $this->dispenser);

        $this->assertTrue($this->aggregate->has('foo'));
    }

    // AggregateInterface::get()
    /** @test */
    function it_provides_access_to_a_specific_dispenser()
    {
        $this->aggregate->add('foo', $this->dispenser);

        $bar = new Dispenser(function () {});
        $this->aggregate->add('bar', $bar);

        $this->assertSame($this->dispenser, $this->aggregate->get('foo'));
        $this->assertSame($bar, $this->aggregate->get('bar'));
    }

    // AggregateInterface::all()
    /** @test */
    function it_provides_all_the_dispensers()
    {
        $this->aggregate->add('foo', $this->dispenser);
        $this->aggregate->add('bar', $this->dispenser);

        $this->assertEquals([
            'foo' => $this->dispenser,
            'bar' => $this->dispenser,
        ], $this->aggregate->all());
    }

    // AggregateInterface::remove()
    /** @test */
    function it_removes_a_specific_dispenser()
    {
        $this->aggregate->add('foo', $this->dispenser);
        $this->aggregate->add('bar', $this->dispenser);

        $this->aggregate->remove('foo');

        $this->assertFalse($this->aggregate->has('foo'));
        $this->assertTrue($this->aggregate->has('bar'));
    }

    // AggregateInterface::clear()
    /** @test */
    function it_clears_all_dispensers()
    {
        $this->aggregate->add('foo', $this->dispenser);
        $this->aggregate->add('bar', $this->dispenser);

        $this->aggregate->clear();

        $this->assertCount(0, $this->aggregate);
    }

    /** @test */
    function it_overwrites_a_previous_dispenser()
    {
        $this->aggregate->add('foo', $this->dispenser);

        $bar = new Dispenser(function () {});
        $this->aggregate->add('foo', $bar);

        $this->assertCount(1, $this->aggregate);

        $this->assertSame($bar, $this->aggregate->get('foo'));
    }

    /** @test */
    function it_can_iterate_over_the_dispensers()
    {
        $dispensers = [
            'foo' => $this->dispenser,
            'bar' => new Dispenser(function () {}),
        ];

        $this->aggregate->add('foo', $dispensers['foo']);
        $this->aggregate->add('bar', $dispensers['bar']);

        foreach ($this->aggregate as $slug => $dispenser) {
            $this->assertSame($dispensers[$slug], $dispenser);
        }
    }

    /** @test */
    function it_throws_a_NotFoundException_when_accessing_a_dispenser_that_does_not_exist()
    {
        $this->setExpectedException(NotFoundException::class);

        $this->aggregate->get('foo');
    }

    /** @test */
    function it_throws_a_NotFoundException_after_accessing_a_dispenser_that_got_cleared()
    {
        $this->setExpectedException(NotFoundException::class);

        $this->aggregate->add('foo', $this->dispenser);

        $this->assertCount(1, $this->aggregate);
        $this->assertSame($this->dispenser, $this->aggregate->get('foo'));

        $this->aggregate->clear();

        $this->aggregate->get('foo');
    }
}
