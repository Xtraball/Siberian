# apns2

Simple & expressive PHP HTTP2 API for Apple Push Notification service (APNs) with comprehensive docs/constants.

### Guidance

1. [Make Sure CURL Supports HTTP/2](http://stackoverflow.com/a/34831873/286348)
2. [Creating a Universal Push Notification Client SSL Certificate (.p12)](https://developer.apple.com/library/ios/documentation/IDEs/Conceptual/AppDistributionGuide/AddingCapabilities/AddingCapabilities.html#//apple_ref/doc/uid/TP40012582-CH26-SW11)
3. Converting .p12 to .pem
> `openssl pkcs12 -in cert.p12 -out cert.pem -nodes -clcerts`
4. Go
> `composer require dashi/apns2`


### Usage

#### using classes

```php
$connection = new Dashi\Apns2\Connection();
$connection->sandbox = false;
$connection->certPath = '/path/to/http2/cert.pem';

$aps = new Dashi\Apns2\MessageAPSBody();
$aps->alert = 'test 1';
$aps->sound = 'default';

$message = new Dashi\Apns2\Message();
$message->aps = $aps;

$options = new Dashi\Apns2\Options();
$options->apnsTopic = 'your.bundle.id';

$responses = $connection->send([
    '81fbf7e296f6c94755832a48476182e4e9586a380116e18a46531b62349504f0',
    'e2d0b464813b6b2371d745dff2b1e5fb6b83b07f7dcd98cc9f1346a7752dcc45',
], $message, $options);
$connection->close();
var_dump($responses);
```

#### using arrays

```php
$connection = new \Dashi\Apns2\Connection(['sandbox' => true, 'cert-path' => '/path/to/http2/cert.pem']);

$responses = $connection->send([
    '81fbf7e296f6c94755832a48476182e4e9586a380116e18a46531b62349504f1' // invalid
], [
    'aps' => [
        'alert' => 'test 2',
        'sound' => 'default',
    ]
], [
    'apns-topic' => 'your.bundle.id',
]);
$connection->close();
```
#### check response data
```php
echo "check response: {$responses[0]->apnsId} == ${uuid}\n";
assert($responses[0]->apnsId == $uuid);

$reason = \Dashi\Apns2\Response::REASON_BAD_DEVICE_TOKEN;
echo "check response: {$responses[0]->reason} == ${reason}\n";
assert($responses[0]->reason == $reason);
```
### License

MIT
