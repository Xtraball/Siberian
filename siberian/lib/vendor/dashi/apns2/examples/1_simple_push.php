<?php
require_once __DIR__ . '/../vendor/autoload.php';

$connection = new Dashi\Apns2\Connection();
$connection->sandbox = false;
$connection->certPath = '/path/to/http2/cert.pem';

$aps = new Dashi\Apns2\MessageAPSBody();
$aps->alert = 'test 1';
$aps->sound = 'default';

$message = new Dashi\Apns2\Message();
$message->aps = $aps;

$options = new Dashi\Apns2\Options();
$options->apnsTopic = 'com.ohsame.same2.0';

$responses = $connection->send([
    '81fbf7e296f6c94755832a48476182e4e9586a380116e18a46531b62349504f0',
    'e2d0b464813b6b2371d745dff2b1e5fb6b83b07f7dcd98cc9f1346a7752dcc45',
], $message, $options);
$connection->close();
var_dump($responses);