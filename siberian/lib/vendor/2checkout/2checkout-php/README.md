2Checkout PHP Library
=====================

This library provides developers with a simple set of bindings to the 2Checkout Payment API, Hosted Checkout, Instant Notification Service and Admin API.

To use, download or clone the repository.

```shell
git clone https://github.com/2Checkout/2checkout-php.git
```

Require in your php script.

```php
require_once("/path/to/2checkout-php/lib/Twocheckout.php");
```

All methods return an Array by default or you can set the format to 'json' to get a JSON response.
**Example:**
```php
<?php
Twocheckout::format('json');
```


Credentials and Options
-----------------

Methods are provided to set the following credentials and options.

```php
<?php

// Your sellerId(account number) and privateKey are required to make the Payment API Authorization call.
Twocheckout::privateKey('BE632CB0-BB29-11E3-AFB6-D99C28100996');
Twocheckout::sellerId('901248204');

// Your username and password are required to make any Admin API call.
Twocheckout::username('testlibraryapi901248204');
Twocheckout::password('testlibraryapi901248204PASS');

// If you want to turn off SSL verification (Please don't do this in your production environment)
Twocheckout::verifySSL(false);  // this is set to true by default

// To use your sandbox account set sandbox to true
Twocheckout::sandbox(true);

// All methods return an Array by default or you can set the format to 'json' to get a JSON response.
Twocheckout::format('json');

```



Full documentation for each binding is provided in the **[wiki](https://github.com/2Checkout/2checkout-php/wiki)**.

Example Payment API Usage
-----------------

*Example Request:*

```php
<?php

Twocheckout::privateKey('BE632CB0-BB29-11E3-AFB6-D99C28100996');
Twocheckout::sellerId('901248204');

try {
    $charge = Twocheckout_Charge::auth(array(
        "sellerId" => "901248204",
        "merchantOrderId" => "123",
        "token" => 'MjFiYzIzYjAtYjE4YS00ZmI0LTg4YzYtNDIzMTBlMjc0MDlk',
        "currency" => 'USD',
        "total" => '10.00',
        "billingAddr" => array(
            "name" => 'Testing Tester',
            "addrLine1" => '123 Test St',
            "city" => 'Columbus',
            "state" => 'OH',
            "zipCode" => '43123',
            "country" => 'USA',
            "email" => 'testingtester@2co.com',
            "phoneNumber" => '555-555-5555'
        ),
        "shippingAddr" => array(
            "name" => 'Testing Tester',
            "addrLine1" => '123 Test St',
            "city" => 'Columbus',
            "state" => 'OH',
            "zipCode" => '43123',
            "country" => 'USA',
            "email" => 'testingtester@2co.com',
            "phoneNumber" => '555-555-5555'
        )
    ));
    $this->assertEquals('APPROVED', $charge['response']['responseCode']);
} catch (Twocheckout_Error $e) {
    $this->assertEquals('Unauthorized', $e->getMessage());
}
```

*Example Response:*

```php
Array
(
    [validationErrors] =>
    [exception] =>
    [response] => Array
        (
            [type] => AuthResponse
            [lineItems] => Array
                (
                    [0] => Array
                        (
                            [options] => Array
                                (
                                )

                            [price] => 10.00
                            [quantity] => 1
                            [recurrence] =>
                            [startupFee] =>
                            [productId] =>
                            [tangible] => N
                            [name] => 123
                            [type] => product
                            [description] =>
                            [duration] =>
                        )

                )

            [transactionId] => 205181140830
            [billingAddr] => Array
                (
                    [addrLine1] => 123 Test St
                    [addrLine2] =>
                    [city] => Columbus
                    [zipCode] => 43123
                    [phoneNumber] => 555-555-5555
                    [phoneExtension] =>
                    [email] => testingtester@2co.com
                    [name] => Testing Tester
                    [state] => OH
                    [country] => USA
                )

            [shippingAddr] => Array
                (
                    [addrLine1] => 123 Test St
                    [addrLine2] =>
                    [city] => Columbus
                    [zipCode] => 43123
                    [phoneNumber] =>
                    [phoneExtension] =>
                    [email] =>
                    [name] => Testing Tester
                    [state] => OH
                    [country] => USA
                )

            [merchantOrderId] => 123
            [orderNumber] => 205181140821
            [recurrentInstallmentId] =>
            [responseMsg] => Successfully authorized the provided credit card
            [responseCode] => APPROVED
            [total] => 10.00
            [currencyCode] => USD
            [errors] =>
        )

)
```

Example Admin API Usage
-----------------

*Example Request:*

```php
<?php

Twocheckout::username('testlibraryapi901248204');
Twocheckout::password('testlibraryapi901248204PASS');

$args = array(
    'sale_id' => 4834917619
);
try {
    $result = Twocheckout_Sale::stop($args);
} catch (Twocheckout_Error $e) {
    $e->getMessage();
}
```

*Example Response:*

```php
<?php

[response_code] => OK
[response_message] => Array
    (
        [0] => 4834917634
        [1] => 4834917646
        [2] => 4834917658
    )
```

Example Checkout Usage:
-----------------------

*Example Request:*

```php
<?php
$params = array(
    'sid' => '1817037',
    'mode' => '2CO',
    'li_0_name' => 'Test Product',
    'li_0_price' => '0.01'
);
Twocheckout_Charge::form($params, 'auto');
```

*Example Response:*
```php
<form id="2checkout" action="https://www.2checkout.com/checkout/spurchase" method="post">
<input type="hidden" name="sid" value="1817037"/>
<input type="hidden" name="mode" value="2CO"/>
<input type="hidden" name="li_0_name" value="Test Product"/>
<input type="hidden" name="li_0_price" value="0.01"/>
<input type="submit" value="Click here if you are not redirected automatically" /></form>
<script type="text/javascript">document.getElementById('2checkout').submit();</script>
```

Example Return Usage:
---------------------

*Example Request:*

```php
<?php

$params = array();
foreach ($_REQUEST as $k => $v) {
    $params[$k] = $v;
}
$passback = Twocheckout_Return::check($params, "tango");
```

*Example Response:*

```php
<?php

[response_code] => Success
[response_message] => Hash Matched
```

Example INS Usage:
------------------

*Example Request:*

```php
<?php

$params = array();
foreach ($_POST as $k => $v) {
    $params[$k] = $v;
}
$passback = Twocheckout_Notification::check($params, "tango");
```

*Example Response:*

```php
<?php

[response_code] => Success
[response_message] => Hash Matched
```

Exceptions:
-----------
Twocheckout_Error exceptions are thrown by if an error has returned. It is best to catch these exceptions so that they can be gracefully handled in your application.

*Example Usage*

```php
<?php

Twocheckout::username('testlibraryapi901248204');
Twocheckout::password('testlibraryapi901248204PASS');

$params = array(
    'sale_id' => 4774380224,
    'category' => 1,
    'comment' => 'Order never sent.'
);
try {
    $sale = Twocheckout_Sale::refund($params);
} catch (Twocheckout_Error $e) {
    $e->getMessage();
}
```

Full documentation for each binding is provided in the **[wiki](https://github.com/2Checkout/2checkout-php/wiki)**.
