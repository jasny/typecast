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

$typecast = new TypeCast();

$typecast->value(null)->to('string'); // null

$typecast->value('987')->to('integer'); // 987
$typecast->value('2015-01-01')->to(DateTime::class); // new DateTime('2015-01-01)
$typecast->value($data)->to(FooBar::class); // FooBar::__set_state($data)

// Unable to cast
$typecast->value('red')->to('float'); // 'red' + triggers a notice
$typecast->value(new stdClass())->to('int'); // stdClass object + triggers a notice
```

### Alias

You can set aliases in cases where you might need to cast to an interface or abstract class or when you want to
cast to a child class.

```php
$typecast = new TypeCast();
$typecast->alias(FooBarInterface::class, FooBar::class);

$typecast->value($data)->to(FooBarInterface::class); // FooBar::__set_state($data)
```

### Errors

By default an `E_NOTICE` is triggered if a value can't be casted to desired type. `Jasny\TypeCast` follows stricter
rules than PHP for casting values.

Instead of a notice an error of any severity can be triggered. Alternatively any `Throwable` like an exception or
error can be thrown. 

```php
$typecast = new TypeCast();

$typecast->failWith(E_USER_WARNING);
$typecast->failWith(TypeError::class);
$typecast->failWith(UnexpectedValueException::class);
```

#### Variable name in error

You can use the `setName()` method to set the property or variable name that is casted. This name will be included in
any error triggered when type casting. This can be useful when determining an issue.

```php
$foo = 'red';
$typecast->value($foo)->setName('foo')->to('float');
```

### Dependency injection

If your application supports dependency injection through containers, create a new `TypeCast` object and add it to the
container as a service.

The `value()` method will clone the `TypeCast` object. Settings like any aliases or custom handlers will propagate.

```php
use Jasny\TypeCast;
use Jasny\TypeCastInterface;

$container = new Container([
  TypeCastInterface::class => function() {
    $typecast = new TypeCast();
    $typecast->alias(FooBarInterface::class, FooBar::class);
    
    return $typecast;
  }
]);

$container->get(TypeCastInterface::class)->value('987')->to('integer');
```

_Assume that `Container` is any [PSR-11 compatible container](https://www.php-fig.org/psr/psr-11/)._

### Handlers

The `Typecast` object uses handlers to cast a value. Each handler can cast a value to a specific type. The following
handlers are defined:

* array _(includes typed arrays as `string[]` and `DateTime[]`)_
* boolean
* float
* integer
* number _(`int|float`)_
* mixed
* object _(includes casting to a specific class)_
* resource
* string
* multiple _(e.g. `int|string` and `string|string[]`)_

You may overwrite the handlers when creating the `TypeCast` object. 
 
#### Desire

The `desire` method will return the handler. This is an alternative approach of using the `value` method. If you need to
cast multiple values to the same type, it's recommendable to get the handler once using `desire`.

```php
use Jasny\TypeCast;

$typecast = new TypeCast();
$typecast->desire('integer')->cast('10');

$arrayHandler = $typecast->desire('array'); 
foreach ($items as &$item) {
  $item = $arrayHandler->cast($item);
}
```
 
#### Multiple handler
In cast multiple types are specified, the handler will try to guess the type the value should be cast in. This might
hurt performance. You may use `NoTypeGuess` to have the handler give an error if the type can't be determined.

```php
use Jasny\TypeCast;

$multipleHandler = new TypeCast\Handlers\MultipleHandler(new TypeCast\NoTypeGuess()); 
$typecast = new TypeCast(null, ['multiple' => $multipleHandler] + TypeCast::getDefaultHandlers());
```
