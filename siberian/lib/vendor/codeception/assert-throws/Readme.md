# AssertThrows

Handle exceptions inside a test without a stop! Works with **PHPUnit** and Codeception.

[![Actions Status](https://github.com/Codeception/AssertThrows/workflows/CI/badge.svg)](https://github.com/Codeception/AssertThrows/actions)
[![Latest Stable Version](https://poser.pugx.org/codeception/Assert-Throws/v/stable)](https://github.com/Codeception/AssertThrows/releases)
[![Total Downloads](https://poser.pugx.org/codeception/Assert-Throws/downloads)](https://packagist.org/packages/codeception/Assert-Throws)
[![License](https://poser.pugx.org/codeception/Assert-Throws/license)](/LICENSE)

## Installation

```
composer require "codeception/assert-throws" --dev
```

Include `AssertThrows` trait it to a TestCase:

```php
<?php

class MyTest extends PHPUnit\Framework\TestCase
{
    use Codeception\AssertThrows;

    //...
} 
```

## Usage

Catch exception thrown inside a code block.

```php
<?php

$this->assertThrows(NotFoundException::class, function() {
	$this->userController->show(99);
});

// alternatively
$this->assertThrows(new NotFoundException(), function() {
	$this->userController->show(99);
});

// you can also assert that an exception is not throw
$this->assertDoesNotThrow(NotFoundException::class, function() {
    $this->userController->show(99);
});
```

You can optionally test the exception message:

```php
<?php

$this->assertThrowsWithMessage(
    NotFoundException::class, 'my error message', function() {
	throw new NotFoundException('my error message');
    }
);
```

## License

`Codeception AssertThrows` is open-sourced software licensed under the [MIT](/LICENSE) License.

© Codeception PHP Testing Framework
