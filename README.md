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

use Bhittani\Dispenser\Queue;
use Bhittani\Dispenser\Dispenser;

$queue = new Queue;

$queue->push(new Dispenser(function ($a, $b) { return $a . $b . 1; }));
$queue->push(new Dispenser(function ($a, $b) { return $a . $b . 2; }));
// Doesn't have to be a dispenser, but recommended.
$queue->push(function ($a, $b) { return $a . $b . 3; });

$queue->dispense('a', 'b'); // ['ab1', 'ab2', 'ab3']
```

### Stack Dispenser

A stack dispenser maintains a collection of dispensers in a stack.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Stack;
use Bhittani\Dispenser\Dispenser;

$stack = new Stack;

$stack->push(new Dispenser(function ($a, $b) { return $a . $b . 1; }));
$stack->push(new Dispenser(function ($a, $b) { return $a . $b . 2; }));
// Doesn't have to be a dispenser, but recommended.
$stack->push(function ($a, $b) { return $a . $b . 3; });

$stack->dispense('a', 'b'); // ['ab3', 'ab2', 'ab1']
```

### Priority Dispenser

A priority dispenser maintains a collection of dispensers in a priority heap.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Priority;
use Bhittani\Dispenser\Dispenser;

$priority = new Priority;

$priority->insert(new Dispenser(function ($a, $b) { return $a . $b . 3; }), 1);
$priority->insert(new Dispenser(function ($a, $b) { return $a . $b . 1; }), 3);
// Doesn't have to be a dispenser, but recommended.
$priority->insert(function ($a, $b) { return $a . $b . 2; }, 2);

$priority->dispense('a', 'b'); // ['ab1', 'ab2', 'ab3']
```

> The rank is directly proportional to the priority value. In the example above,
> priority value 3 dispenses first, followed by 2, and finally by 1.

### Pipeline Dispenser

A pipeline allows piping through dispensers by passing on the result of the previous piped dispenser onto the next piped dispenser.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Stack;
use Bhittani\Dispenser\Pipeline;
use Bhittani\Dispenser\Dispenser;

$pipeline = new Pipeline;

$pipeline->push($stack = new Stack);

$stack->push(new Dispenser(function ($n) { return $n / 5; })); // 100/5=20
$stack->push(new Dispenser(function ($n) { return $n + 60; })); // 40+60=100
$stack->push(function ($n) { return $n * 4; }); // 10*4=40

$pipeline->push(function ($n) { return $n / 2; }); // 20/2=10

$pipeline->dispense(10); // 10
```

> The iterator dispenser's discipline will be respected. i.e. if we were to use
> a priority dispenser, the results would vary due to the potential difference in the order of dispensation.

### Chain Dispenser

A chain accepts dispensers and work on it as a chain. As opposed to a pipeline, a dispenser within a chain receives a handler to the next dispenser in the chain and can be completely ignored in order to stop further processing of the chain.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Chain;
use Bhittani\Dispenser\Dispenser;

// Accepts an optional fallback dispenser.
$chain = new Chain(function ($foo, $bar) {
    return '!';
});

$chain->push(new Dispenser(function ($foo, $bar, $next) {
    return '(' . $next($foo, $bar) . ')';
}));

// Doesn't have to be a dispenser, but recommended.
$chain->push(function ($foo, $bar, $next) {
    return $foo.$next($foo, $bar);
});

$chain->push(function ($foo, $bar, $next) {
    return $next($foo, $bar).$bar;
});

$chain->dispense('middle', 'ware'); // (middle!ware)
```

> Every dispenser's discipline will be respected. i.e. if we were to use
> a stack dispenser, the results would vary due to the potential difference in the order of dispensation.

> Different types of dispensers can be pushed to the same chain.

> The fallback dispenser is optional and does not have a final $next parameter because it will be the last dispenser in the chain.

#### Using the chain dispenser as an http middleware

Due to the powerful discipline of the chain dispenser, we can use it as an http middleware chain.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\Chain;
use Bhittani\Dispenser\Queue;
use Bhittani\Dispenser\Dispenser;

$chain = new Chain(function ($request) {
    return make_a_response_however_you_want_to($request);
});

$chain->push($middlewares = new Queue);

// With a fictional request & response objects,
// lets record the time spent on the request.
$middlewares->push(new Dispenser(function ($request, $next) {
    $request = $request->startTime();
    $response = $next($request);
    $request->stopTime();

    return $response->withTime($request->getElapsedTime());
}));

$middlewares->push(new Dispenser(function ($request, $next) {
    // Do something...
    return $next($request);
}));

// Handle the $request...
$chain->dispense($request);
```

### Creating custom/extended dispensers

Extended dispensers can be created by implementing the `Bhittani\Dispenser\DispenserInterface` interface. It has only one required method `dispense` which accepts variadic arguments. This makes it very simple to mix and utilize other dispensers. The power is in your hands.

#### Example dispenser implementation

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Bhittani\Dispenser\DispenserInterface;

class Dispatcher implements DispenserInterface
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

    public function dispense(...$parameters)
    {
        $key = array_shift($parameters);

        if (! isset($this->subscribers[$key])) {
            return [];
        }

        return array_map(function ($subscriber) use ($parameters) {
            return $subscriber->dispense($parameters);
        }, $this->subscribers[$key]);
    }
}

$dispatcher = new Dispatcher;

$dispatcher->subscribe('foo', new Dispenser(function ($a, $b) {
    return $a.'1foo1'.$b;
}));

$dispatcher->subscribe('foo', new Dispenser(function ($a, $b) {
    return $a.'2foo2'.$b;
}));

$dispatcher->subscribe('bar', new Dispenser(function ($a, $b) {
    return $a.'bar'.$b;
}));

$dispatcher->dispense('bar', 'a', 'b'); // ['abarb']
$dispatcher->dispense('foo', 'a', 'b'); // ['a1foo1b', 'a2foo2b']
```

> The above implementation works as an event subscriber and publisher.

> For availability, the `Dispatcher` is also available under the `Bhittani\Dispenser` namespace.

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
