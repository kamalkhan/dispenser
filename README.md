# Dispenser

[![Travis Build Status][icon-travis-status]][link-travis-status]
[![Packagist Downloads][icon-packagist-downloads]][link-packagist-downloads]
[![License][icon-license]](LICENSE.md)

Dispense entities under a disciplined regime.

- [Dispenser](#dispenser)
  - [Install](#install)
  - [Usage](#usage)
    - [Dispenser](#dispenser-1)
    - [Queue Dispenser](#queue-dispenser)
    - [Stack Dispenser](#stack-dispenser)
    - [Priority Dispenser](#priority-dispenser)
    - [Pipeline Dispenser](#pipeline-dispenser)
    - [Chain Dispenser](#chain-dispenser)
      - [Using the chain dispenser as an http middleware](#using-the-chain-dispenser-as-an-http-middleware)
    - [Aggregate Dispenser](#aggregate-dispenser)
    - [Delegator Dispenser](#delegator-dispenser)
    - [Creating custom/extended dispensers](#creating-customextended-dispensers)
      - [Example dispenser implementation](#example-dispenser-implementation)
  - [Changelog](#changelog)
  - [Testing](#testing)
  - [Contributing](#contributing)
  - [Security](#security)
  - [Credits](#credits)
  - [License](#license)

## Install

You may install this package using [composer][link-composer].

```shell
$ composer require bhittani/dispenser --prefer-dist
```

## Usage

### Dispenser

At its core, the dispenser handles a function call.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;

$dispenser = new Dispenser(function ($a, $b) {
    return $a . '.' . $b;
});

$dispenser->dispense('foo', 'bar'); // 'foo.bar'
```

### Queue Dispenser

A queue dispenser maintains a collection of dispensers in a queue.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;
use Bhittani\Dispenser\QueueDispenser;

$queue = new QueueDispenser;

$queue->push(new Dispenser(function ($a, $b) { return $a . $b . 1; }));
$queue->push(new Dispenser(function ($a, $b) { return $a . $b . 2; }));
// Doesn't have to be a dispenser, but recommended.
$queue->push(function ($a, $b) { return $a . $b . 3; });

$queue->dispense(['a', 'b']); // ['ab1', 'ab2', 'ab3']
```

### Stack Dispenser

A stack dispenser maintains a collection of dispensers in a stack.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;
use Bhittani\Dispenser\StackDispenser;

$stack = new StackDispenser;

$stack->push(new Dispenser(function ($a, $b) { return $a . $b . 1; }));
$stack->push(new Dispenser(function ($a, $b) { return $a . $b . 2; }));
// Doesn't have to be a dispenser, but recommended.
$stack->push(function ($a, $b) { return $a . $b . 3; });

$stack->dispense(['a', 'b']); // ['ab3', 'ab2', 'ab1']
```

### Priority Dispenser

A priority dispenser maintains a collection of dispensers in a priority heap.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;
use Bhittani\Dispenser\PriorityDispenser;

$ranked = new PriorityDispenser;

$ranked->insert(new Dispenser(function ($a, $b) { return $a . $b . 1; }), 2);
$ranked->insert(new Dispenser(function ($a, $b) { return $a . $b . 2; }), 3);
// Doesn't have to be a dispenser, but recommended.
$ranked->insert(function ($a, $b) { return $a . $b . 3; }, 1);

$ranked->dispense(['a', 'b']); // ['ab2', 'ab1', 'ab3']
```

> The rank is directly proportional to the priority value. In the example above,
> priority value 3 dispenses first, followed by 2, and finally by 1.

### Pipeline Dispenser

A pipeline allows piping through an iterator dispenser (queues, stacks, priority heaps) by passing on the result of the previous piped dispenser onto the next piped dispenser.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;
use Bhittani\Dispenser\QueueDispenser;
use Bhittani\Dispenser\PipelineDispenser;

$queue = new QueueDispenser;

$queue->push(new Dispenser(function ($a, $b) { return $a + $b; })); // (1, 2) => 3
$queue->push(new Dispenser(function ($n) { return $n - 5; })); // (3) => -2
// Doesn't have to be a dispenser, but recommended.
$queue->push(function ($n) { return $n * 2; }); // (-2) => -4

$pipeline = new PipelineDispenser($queue);

$pipeline->dispense([1, 2]); // -4
```

> The iterator dispenser's discipline will be respected. i.e. if we were to use
> a stack dispenser, the results would vary due to the difference in the order of dispensation.

### Chain Dispenser

A chain allows usage of an iterator dispenser as a chain. As opposed to a pipeline, a dispenser within a chain receives a handler to the next dispenser in the chain and can be completely ignored in order to stop further processing of the chain.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;
use Bhittani\Dispenser\QueueDispenser;
use Bhittani\Dispenser\ChainDispenser;

$queue = new QueueDispenser;

$queue->push(new Dispenser(function ($str, $next) {
    return '1f' . $next($str) . '1l';
}));

$queue->push(new Dispenser(function ($str, $next) {
    return '2f' . $next($str) . '2l';
}));

// Doesn't have to be a dispenser, but recommended.
$queue->push(function ($str, $next) {
    return '3f' . $next($str) . '3l';
});

$chain = new ChainDispenser($queue, function () {
    // $fallback dispenser.
    return 0;
});

$chain->dispense([2])); // '1f2f3f03l2l1l'
```

> The iterator dispenser's discipline will be respected. i.e. if we were to use
> a stack dispenser, the results would vary due to the difference in the order of dispensation.

> The fallback dispenser is optional.

#### Using the chain dispenser as an http middleware

Due to the powerful discipline of the chain dispenser, we can use it as an http middleware chain.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;
use Bhittani\Dispenser\QueueDispenser;
use Bhittani\Dispenser\ChainDispenser;

$middlewareQueue = new QueueDispenser;

$middlewareQueue->push(new Dispenser(function ($request, $next) {
    // ...
}));

$middlewareQueue->push(new Dispenser(function ($request, $next) {
    // ...
}));

$chain = new ChainDispenser($middlewareQueue);

// Handle the $request...
$chain->dispense([$request]);
```

### Aggregate Dispenser

An aggregate stores dispensers tagged by a unique key/slug.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;
use Bhittani\Dispenser\AggregateDispenser;

$aggregate = new AggregateDispenser;

// Add a dispenser.
$aggregate->add('lc', new Dispenser(function ($str) {
    return strtolower($str);
}));

// Add a built-in php function string as a dispenser.
$aggregate->add('uc', new Dispenser('strtoupper');

$aggregate->has('foo'); // false

$aggregate->has('lc'); // true

$toLowerCase = $aggregate->get('lc');
$toLowerCase->dispense(['FoO']); // foo

$toUpperCase = $aggregate->get('uc');
$toUppercase->dispense(['FoO']); // FOO
```

> The aggregate keeps a unique key/slug store. A collision replaces the previous delegate at the specified key.

### Delegator Dispenser

A delegator accepts aggregate dispenser delegations. This is more practically used for accepting fallback aggregate lookups that are provided by strangers in your library.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Dispenser;
use Bhittani\Dispenser\AggregateDispenser;
use Bhittani\Dispenser\DelegatorDispenser;

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

$delegator->dispense(['foo', 'BaR']); // bar
```

### Creating custom/extended dispensers

Extended dispensers can be created by implementing the `Bhittani\Dispenser\DispenserInterface` interface. It has only one required method `dispense` which accepts an array. This makes it very simple to mix and utilize other dispensers. The power is in your hands.

#### Example dispenser implementation

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\DispenserInterface;

class EventDispenser implements DispenserInterface
{
    protected $subscribers = [];

    public function subscribe($key, DispenserInterface $subscriber)
    {
        if (! isset($this->subscribers[$key])) {
            $this->subscribers[$key] = [];
        }

        $this->subscribers[$key][] = $subscriber;

        return $this;
    }

    public function dispense(array $args)
    {
        $key = array_shift($args);

        $responses = [];

        if (! isset($this->subscribers[$key])) {
            return $responses;
        }

        foreach ($this->subscribers[$key] as $subscriber) {
            $responses[] = $subscriber->dispense($args);
        }

        return $responses;
    }
}

$event = new EventDispenser;

$event->subscribe('foo', new Dispenser(function ($a, $b) {
    return 'foo' . $a . $b;
}));

$event->subscribe('bar', new Dispenser(function ($a, $b) {
    return 'bar' . $a . $b;
}));

$event->dispense(['foo', 'a', 'b']); // [fooab]

$event->dispense(['bar', 'a', 'b']); // [barab]
```

> The above implementation works as an event subscriber and publisher.

> For availability, the `EventDispenser` is also available under the `Bhittani\Dispenser` namespace.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed.

## Testing

```shell
$ git clone https://github.com/kamalkhan/dispenser
$ composer install
$ composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](.github/CONDUCT.md) for details.

## Security

If you discover any security related issues, please email `shout@bhittani.com` instead of using the issue tracker.

## Credits

- [Kamal Khan](http://bhittani.com)
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.

<!-- Travis Status -->
[icon-travis-status]: https://img.shields.io/travis/kamalkhan/dispenser.svg?style=flat-square
[link-travis-status]: https://travis-ci.org/kamalkhan/dispenser
<!-- Packagist Downloads -->
[icon-packagist-downloads]: https://img.shields.io/packagist/dt/bhittani/dispenser.svg?style=flat-square
[link-packagist-downloads]: https://packagist.org/packages/bhittani/dispenser
<!-- License -->
[icon-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
<!-- Composer -->
[link-composer]: https://getcomposer.org
<!-- Contributors -->
[link-contributors]: https://github.com/kamalkhan/dispenser/contributors
