Jasny TypeCast
===

[![Build Status](https://travis-ci.org/jasny/typecast.svg?branch=master)](https://travis-ci.org/jasny/typecast)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/typecast/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/typecast/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/typecast/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/typecast/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d50691f2-4bcb-4cf7-995a-098a2ce478ac/mini.png)](https://insight.sensiolabs.com/projects/d50691f2-4bcb-4cf7-995a-098a2ce478ac)

This library does [type casting in PHP](http://php.net/manual/en/language.types.type-juggling.php#language.types.typecasting).

Type casting is natively supported in PHP. This library adds some basic logic to the process, like triggering a warning when
casting `"FOO"` to an integer.

In contrary to PHP's internal type casting, casting `null` always results in `null`.

Installation
---

The Jasny TypeCast package is available on [packagist](https://packagist.org/packages/jasny/meta). Install it using
composer:

    composer require jasny/typecast


Usage
---

```php
Jasny\TypeCast::value(null)->to('string'); // null

Jasny\TypeCast::value('987')->to('integer'); // 987
Jasny\TypeCast::value('2015-01-01')->to('DateTime'); // DateTime object
Jasny\TypeCast::value($data)->to('FooBar'); // FooBar object

// Unable to cast
Jasny\TypeCast::cast('red', 'float'); // 'red' + triggers a notice
Jasny\TypeCast::cast(new stdClass(), 'int'); // stdClass object + triggers a notice
```

