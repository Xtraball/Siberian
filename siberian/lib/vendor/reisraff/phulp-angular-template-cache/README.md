# phulp-angular-filesort

The angular-filesort addon for [PHULP](https://github.com/reisraff/phulp).

It's like [gulp-angular-filesort](https://github.com/klei/gulp-angular-filesort) with some modifications.

## Install

```bash
$ composer require reisraff/phulp-angular-filesort
```

## Usage

```php
<?php

use Phulp\AngularFileSort\AngularFileSort;

$phulp->task('angular-filesort', function ($phulp) {
    $phulp->src(['src/'], '/js$/')
        ->pipe(new AngularFileSort);
});

```