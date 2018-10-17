Sanitizator for PHP
=======================

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-sanitize/v/stable.svg)](https://packagist.org/packages/romeOz/rock-sanitize)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-sanitize/downloads.svg)](https://packagist.org/packages/romeOz/rock-sanitize)
[![Build Status](https://travis-ci.org/romeOz/rock-sanitize.svg?branch=master)](https://travis-ci.org/romeOz/rock-sanitize)
[![HHVM Status](http://hhvm.h4cc.de/badge/romeoz/rock-sanitize.svg)](http://hhvm.h4cc.de/package/romeoz/rock-sanitize)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-sanitize/badge.svg?branch=master)](https://coveralls.io/r/romeOz/rock-sanitize?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-sanitize/license.svg)](https://packagist.org/packages/romeOz/rock-sanitize)

Features
-------------------

 * Sanitization of scalar variable, array and object
 * Custom rules
 * Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)
 
Installation
-------------------

From the Command Line:

```
composer require romeoz/rock-sanitize
```

In your composer.json:

```json
{
    "require": {
        "romeoz/rock-sanitize": "*"
    }
}
```

Quick Start
-------------------

```php
use rock\sanitize\Sanitize;

Sanitize::removeTags()
    ->lowercase()
    ->sanitize('<b>Hello World!</b>');
// output: hello world!    
```

####As Array or Object

```php
use rock\sanitize\Sanitize;

$input = [
    'name' => '<b>Tom</b>',
    'age' => -22
];

$attributes = [
    'name' => Sanitize::removeTags(),
    'age' => Sanitize::abs()
];
        
Sanitize::attributes($attributes)->sanitize($input);
/*
output:

[
  'name' => 'Tom',
  'age' => 22
]
*/

// all attributes:
Sanitize::attributes(Sanitize::removeTags())->sanitize($input);
```

Documentation
-------------------

 * [Rules](https://github.com/romeOz/rock-sanitize/blob/master/docs/rules.md)
 * [Custom rules](https://github.com/romeOz/rock-sanitize/blob/master/docs/custom-rules.md)

[Demo](https://github.com/romeOz/docker-rock-sanitize)
-------------------

 * [Install Docker](https://docs.docker.com/installation/) or [askubuntu](http://askubuntu.com/a/473720)
 * `docker run --name demo -d -p 8080:80 romeoz/docker-rock-sanitize`
 * Open demo [http://localhost:8080/](http://localhost:8080/)

Requirements
-------------------

 * PHP 5.4+

License
-------------------

The Rock Sanitize is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).