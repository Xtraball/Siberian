# wordpress-rest-api-client

> A Wordpress REST API client for PHP

[![Travis](https://img.shields.io/travis/varsitynewsnetwork/wordpress-rest-api-client.svg?maxAge=2592000?style=flat-square)](https://travis-ci.org/varsitynewsnetwork/wordpress-rest-api-client)

For when you need to make [Wordpress REST API calls](http://v2.wp-api.org/) from
some other PHP project, for some reason.

## Installation

This library can be installed with [Composer](https://getcomposer.org):

```text
composer require vnn/wordpress-rest-api-client
```

The library will require an Http library to run. [Guzzle](http://guzzlephp.org) is 
supported by the library, but you can use any Http library of your choise, so long
as your write an adapter for that library.

To install Guzzle:

```text
composer require guzzlehttp/guzzle
```

## Usage

Example:

```php
use Vnn\WpApiClient\Auth\WpBasicAuth;
use Vnn\WpApiClient\Http\GuzzleAdapter;
use Vnn\WpApiClient\WpClient;

require 'vendor/autoload.php';

$client = new WpClient(new GuzzleAdapter(new GuzzleHttp\Client()), 'http://yourwordpress.com');
$client->setCredentials(new WpBasicAuth('user', 'securepassword'));

$user = $client->users()->get(2);

print_r($user);
```

## Testing
```bash
composer install
vendor/bin/peridot
```
