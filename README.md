Jasny TypeCast
===

[![Build Status](https://travis-ci.org/jasny/typecast.svg?branch=master)](https://travis-ci.org/jasny/typecast)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/typecast/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/typecast/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/typecast/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/typecast/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d50691f2-4bcb-4cf7-995a-098a2ce478ac/mini.png)](https://insight.sensiolabs.com/projects/d50691f2-4bcb-4cf7-995a-098a2ce478ac)

This library does [type casting in PHP](http://php.net/manual/en/language.types.type-juggling.php#language.types.typecasting).

Type casting is natively supported in PHP. This library adds some basic logic to the process, like triggering a warning
when casting a string like `"FOO"` to an integer.

In contrary to PHP's internal type casting, casting `null` always results in `null`.

Installation
---

The Jasny TypeCast package is available on [packagist](https://packagist.org/packages/jasny/meta). Install it using
composer:

    composer require jasny/typecast


Usage
---

```php
use Jasny\TypeCast;

TypeCast::value(null)->to('string'); // null

TypeCast::value('987')->to('integer'); // 987
TypeCast::value('2015-01-01')->to(DateTime::class); // new DateTime('2015-01-01)
TypeCast::value($data)->to(FooBar::class); // FooBar::__set_state($data)

// Unable to cast
TypeCast::value('red')->to('float'); // 'red' + triggers a notice
TypeCast::value(new stdClass())->to('int'); // stdClass object + triggers a notice
```

### Dependency injection

If your application supports dependency injection through containers, create a new `TypeCast` object and use the
`forValue()` method, rather than the static `value` method.

The `forValue()` method will clone the `TypeCast` object. This means any aliases will propagate.

```php
use Jasny\TypeCast;
use Jasny\TypeCastInterface;

$container = new Container([
  TypeCastInterface::class => function() {
    return new TypeCast();
  }
]);

$container->get(TypeCastInterface::class)->forValue('987')->to('integer');
```

_Assume that `Container` is a basic [PSR-11 compatible container](https://www.php-fig.org/psr/psr-11/)._

### Alias

You can set aliases in cases where you might need to cast to an interface or abstract class or when you want to
cast to a child class.

```php
$typecast = new TypeCast();
$typecast->alias(FooBarInterface::class, FooBar::class);

$typecast->forValue($data)->to(FooBarInterface::class); // FooBar::__set_state($data)
```

### Variable name in notice message

You can use the `setName()` method to set the property or variable name that is casted. This name will be included in
any notice triggered when type casting. This can be useful when determining an issue.

```php
$foo = 'red';
TypeCast::value($foo)->setName('foo')->to('float');
```
