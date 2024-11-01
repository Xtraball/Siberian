# onesignal\client\DefaultApi

All URIs are relative to https://onesignal.com/api/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**beginLiveActivity()**](DefaultApi.md#beginLiveActivity) | **POST** /apps/{app_id}/live_activities/{activity_id}/token | Start Live Activity
[**cancelNotification()**](DefaultApi.md#cancelNotification) | **DELETE** /notifications/{notification_id} | Stop a scheduled or currently outgoing notification
[**createApp()**](DefaultApi.md#createApp) | **POST** /apps | Create an app
[**createNotification()**](DefaultApi.md#createNotification) | **POST** /notifications | Create notification
[**createPlayer()**](DefaultApi.md#createPlayer) | **POST** /players | Add a device
[**createSegments()**](DefaultApi.md#createSegments) | **POST** /apps/{app_id}/segments | Create Segments
[**createSubscription()**](DefaultApi.md#createSubscription) | **POST** /apps/{app_id}/users/by/{alias_label}/{alias_id}/subscriptions | 
[**createUser()**](DefaultApi.md#createUser) | **POST** /apps/{app_id}/users | 
[**deleteAlias()**](DefaultApi.md#deleteAlias) | **DELETE** /apps/{app_id}/users/by/{alias_label}/{alias_id}/identity/{alias_label_to_delete} | 
[**deletePlayer()**](DefaultApi.md#deletePlayer) | **DELETE** /players/{player_id} | Delete a user record
[**deleteSegments()**](DefaultApi.md#deleteSegments) | **DELETE** /apps/{app_id}/segments/{segment_id} | Delete Segments
[**deleteSubscription()**](DefaultApi.md#deleteSubscription) | **DELETE** /apps/{app_id}/subscriptions/{subscription_id} | 
[**deleteUser()**](DefaultApi.md#deleteUser) | **DELETE** /apps/{app_id}/users/by/{alias_label}/{alias_id} | 
[**endLiveActivity()**](DefaultApi.md#endLiveActivity) | **DELETE** /apps/{app_id}/live_activities/{activity_id}/token/{subscription_id} | Stop Live Activity
[**exportEvents()**](DefaultApi.md#exportEvents) | **POST** /notifications/{notification_id}/export_events?app_id&#x3D;{app_id} | Export CSV of Events
[**exportPlayers()**](DefaultApi.md#exportPlayers) | **POST** /players/csv_export?app_id&#x3D;{app_id} | Export CSV of Players
[**fetchAliases()**](DefaultApi.md#fetchAliases) | **GET** /apps/{app_id}/subscriptions/{subscription_id}/user/identity | 
[**fetchUser()**](DefaultApi.md#fetchUser) | **GET** /apps/{app_id}/users/by/{alias_label}/{alias_id} | 
[**fetchUserIdentity()**](DefaultApi.md#fetchUserIdentity) | **GET** /apps/{app_id}/users/by/{alias_label}/{alias_id}/identity | 
[**getApp()**](DefaultApi.md#getApp) | **GET** /apps/{app_id} | View an app
[**getApps()**](DefaultApi.md#getApps) | **GET** /apps | View apps
[**getEligibleIams()**](DefaultApi.md#getEligibleIams) | **GET** /apps/{app_id}/subscriptions/{subscription_id}/iams | 
[**getNotification()**](DefaultApi.md#getNotification) | **GET** /notifications/{notification_id} | View notification
[**getNotificationHistory()**](DefaultApi.md#getNotificationHistory) | **POST** /notifications/{notification_id}/history | Notification History
[**getNotifications()**](DefaultApi.md#getNotifications) | **GET** /notifications | View notifications
[**getOutcomes()**](DefaultApi.md#getOutcomes) | **GET** /apps/{app_id}/outcomes | View Outcomes
[**getPlayer()**](DefaultApi.md#getPlayer) | **GET** /players/{player_id} | View device
[**getPlayers()**](DefaultApi.md#getPlayers) | **GET** /players | View devices
[**identifyUserByAlias()**](DefaultApi.md#identifyUserByAlias) | **PATCH** /apps/{app_id}/users/by/{alias_label}/{alias_id}/identity | 
[**identifyUserBySubscriptionId()**](DefaultApi.md#identifyUserBySubscriptionId) | **PATCH** /apps/{app_id}/subscriptions/{subscription_id}/user/identity | 
[**transferSubscription()**](DefaultApi.md#transferSubscription) | **PATCH** /apps/{app_id}/subscriptions/{subscription_id}/owner | 
[**updateApp()**](DefaultApi.md#updateApp) | **PUT** /apps/{app_id} | Update an app
[**updateLiveActivity()**](DefaultApi.md#updateLiveActivity) | **POST** /apps/{app_id}/live_activities/{activity_id}/notifications | Update a Live Activity via Push
[**updatePlayer()**](DefaultApi.md#updatePlayer) | **PUT** /players/{player_id} | Edit device
[**updatePlayerTags()**](DefaultApi.md#updatePlayerTags) | **PUT** /apps/{app_id}/users/{external_user_id} | Edit tags with external user id
[**updateSubscription()**](DefaultApi.md#updateSubscription) | **PATCH** /apps/{app_id}/subscriptions/{subscription_id} | 
[**updateUser()**](DefaultApi.md#updateUser) | **PATCH** /apps/{app_id}/users/by/{alias_label}/{alias_id} | 


## `beginLiveActivity()`

```php
beginLiveActivity($app_id, $activity_id, $begin_live_activity_request)
```

Start Live Activity

Starts a Live Activity

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The OneSignal App ID for your app.  Available in Keys & IDs.
$activity_id = 'activity_id_example'; // string | Live Activity record ID
$begin_live_activity_request = new \onesignal\client\model\BeginLiveActivityRequest(); // \onesignal\client\model\BeginLiveActivityRequest

try {
    $apiInstance->beginLiveActivity($app_id, $activity_id, $begin_live_activity_request);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->beginLiveActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The OneSignal App ID for your app.  Available in Keys &amp; IDs. |
 **activity_id** | **string**| Live Activity record ID |
 **begin_live_activity_request** | [**\onesignal\client\model\BeginLiveActivityRequest**](../Model/BeginLiveActivityRequest.md)|  |

### Return type

void (empty response body)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `cancelNotification()`

```php
cancelNotification($app_id, $notification_id): \onesignal\client\model\CancelNotificationSuccessResponse
```

Stop a scheduled or currently outgoing notification

Used to stop a scheduled or currently outgoing notification

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$notification_id = 'notification_id_example'; // string

try {
    $result = $apiInstance->cancelNotification($app_id, $notification_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->cancelNotification: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **notification_id** | **string**|  |

### Return type

[**\onesignal\client\model\CancelNotificationSuccessResponse**](../Model/CancelNotificationSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createApp()`

```php
createApp($app): \onesignal\client\model\App
```

Create an app

Creates a new OneSignal app

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: user_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app = new \onesignal\client\model\App(); // \onesignal\client\model\App

try {
    $result = $apiInstance->createApp($app);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->createApp: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app** | [**\onesignal\client\model\App**](../Model/App.md)|  |

### Return type

[**\onesignal\client\model\App**](../Model/App.md)

### Authorization

[user_key](../../README.md#user_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createNotification()`

```php
createNotification($notification): \onesignal\client\model\CreateNotificationSuccessResponse
```

Create notification

Sends notifications to your users

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$notification = new \onesignal\client\model\Notification(); // \onesignal\client\model\Notification

try {
    $result = $apiInstance->createNotification($notification);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->createNotification: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **notification** | [**\onesignal\client\model\Notification**](../Model/Notification.md)|  |

### Return type

[**\onesignal\client\model\CreateNotificationSuccessResponse**](../Model/CreateNotificationSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createPlayer()`

```php
createPlayer($player): \onesignal\client\model\CreatePlayerSuccessResponse
```

Add a device

Register a new device to one of your OneSignal apps &#x1F6A7; Don't use this This API endpoint is designed to be used from our open source Mobile and Web Push SDKs. It is not designed for developers to use it directly, unless instructed to do so by OneSignal support. If you use this method instead of our SDKs, many OneSignal features such as conversion tracking, timezone tracking, language detection, and rich-push won't work out of the box. It will also make it harder to identify possible setup issues. This method is used to register a new device with OneSignal. If a device is already registered with the specified identifier, then this will update the existing device record instead of creating a new one. The returned player is a player / user ID. Use the returned ID to send push notifications to this specific user later, or to include this player when sending to a set of users. &#x1F6A7; iOS Must set test_type to 1 when building your iOS app as development. Omit this field in your production app builds.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$player = new \onesignal\client\model\Player(); // \onesignal\client\model\Player

try {
    $result = $apiInstance->createPlayer($player);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->createPlayer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **player** | [**\onesignal\client\model\Player**](../Model/Player.md)|  |

### Return type

[**\onesignal\client\model\CreatePlayerSuccessResponse**](../Model/CreatePlayerSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createSegments()`

```php
createSegments($app_id, $segment): \onesignal\client\model\CreateSegmentSuccessResponse
```

Create Segments

Create segments visible and usable in the dashboard and API - Required: OneSignal Paid Plan The Create Segment method is used when you want your server to programmatically create a segment instead of using the OneSignal Dashboard UI. Just like creating Segments from the dashboard you can pass in filters with multiple \"AND\" or \"OR\" operator's. &#x1F6A7; Does Not Update Segments This endpoint will only create segments, it does not edit or update currently created Segments. You will need to use the Delete Segments endpoint and re-create it with this endpoint to edit.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The OneSignal App ID for your app.  Available in Keys & IDs.
$segment = new \onesignal\client\model\Segment(); // \onesignal\client\model\Segment

try {
    $result = $apiInstance->createSegments($app_id, $segment);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->createSegments: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The OneSignal App ID for your app.  Available in Keys &amp; IDs. |
 **segment** | [**\onesignal\client\model\Segment**](../Model/Segment.md)|  | [optional]

### Return type

[**\onesignal\client\model\CreateSegmentSuccessResponse**](../Model/CreateSegmentSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createSubscription()`

```php
createSubscription($app_id, $alias_label, $alias_id, $create_subscription_request_body): \onesignal\client\model\InlineResponse201
```



Creates a new Subscription under the User provided. Useful to add email addresses and SMS numbers to the User.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$alias_label = 'alias_label_example'; // string
$alias_id = 'alias_id_example'; // string
$create_subscription_request_body = new \onesignal\client\model\CreateSubscriptionRequestBody(); // \onesignal\client\model\CreateSubscriptionRequestBody

try {
    $result = $apiInstance->createSubscription($app_id, $alias_label, $alias_id, $create_subscription_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->createSubscription: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **alias_label** | **string**|  |
 **alias_id** | **string**|  |
 **create_subscription_request_body** | [**\onesignal\client\model\CreateSubscriptionRequestBody**](../Model/CreateSubscriptionRequestBody.md)|  |

### Return type

[**\onesignal\client\model\InlineResponse201**](../Model/InlineResponse201.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createUser()`

```php
createUser($app_id, $user): \onesignal\client\model\User
```



Creates a User, optionally Subscriptions owned by the User as well as Aliases. Aliases provided in the payload will be used to look up an existing User.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$user = new \onesignal\client\model\User(); // \onesignal\client\model\User

try {
    $result = $apiInstance->createUser($app_id, $user);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->createUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **user** | [**\onesignal\client\model\User**](../Model/User.md)|  |

### Return type

[**\onesignal\client\model\User**](../Model/User.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteAlias()`

```php
deleteAlias($app_id, $alias_label, $alias_id, $alias_label_to_delete): \onesignal\client\model\InlineResponse200
```



Deletes an alias by alias label

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$alias_label = 'alias_label_example'; // string
$alias_id = 'alias_id_example'; // string
$alias_label_to_delete = 'alias_label_to_delete_example'; // string

try {
    $result = $apiInstance->deleteAlias($app_id, $alias_label, $alias_id, $alias_label_to_delete);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->deleteAlias: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **alias_label** | **string**|  |
 **alias_id** | **string**|  |
 **alias_label_to_delete** | **string**|  |

### Return type

[**\onesignal\client\model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deletePlayer()`

```php
deletePlayer($app_id, $player_id): \onesignal\client\model\DeletePlayerSuccessResponse
```

Delete a user record

Delete player - Required: Used to delete a single, specific Player ID record from a specific OneSignal app.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The OneSignal App ID for your app.  Available in Keys & IDs.
$player_id = 'player_id_example'; // string | The OneSignal player_id

try {
    $result = $apiInstance->deletePlayer($app_id, $player_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->deletePlayer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The OneSignal App ID for your app.  Available in Keys &amp; IDs. |
 **player_id** | **string**| The OneSignal player_id |

### Return type

[**\onesignal\client\model\DeletePlayerSuccessResponse**](../Model/DeletePlayerSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteSegments()`

```php
deleteSegments($app_id, $segment_id): \onesignal\client\model\DeleteSegmentSuccessResponse
```

Delete Segments

Delete segments (not user devices) - Required: OneSignal Paid Plan You can delete a segment under your app by calling this API. You must provide an API key in the Authorization header that has admin access on the app. The segment_id can be found in the URL of the segment when viewing it in the dashboard.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The OneSignal App ID for your app.  Available in Keys & IDs.
$segment_id = 'segment_id_example'; // string | The segment_id can be found in the URL of the segment when viewing it in the dashboard.

try {
    $result = $apiInstance->deleteSegments($app_id, $segment_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->deleteSegments: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The OneSignal App ID for your app.  Available in Keys &amp; IDs. |
 **segment_id** | **string**| The segment_id can be found in the URL of the segment when viewing it in the dashboard. |

### Return type

[**\onesignal\client\model\DeleteSegmentSuccessResponse**](../Model/DeleteSegmentSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteSubscription()`

```php
deleteSubscription($app_id, $subscription_id)
```



Deletes the Subscription.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$subscription_id = 'subscription_id_example'; // string

try {
    $apiInstance->deleteSubscription($app_id, $subscription_id);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->deleteSubscription: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **subscription_id** | **string**|  |

### Return type

void (empty response body)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteUser()`

```php
deleteUser($app_id, $alias_label, $alias_id)
```



Removes the User identified by (:alias_label, :alias_id), and all Subscriptions and Aliases

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$app_id = 'app_id_example'; // string
$alias_label = 'alias_label_example'; // string
$alias_id = 'alias_id_example'; // string

try {
    $apiInstance->deleteUser($app_id, $alias_label, $alias_id);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->deleteUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **alias_label** | **string**|  |
 **alias_id** | **string**|  |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `endLiveActivity()`

```php
endLiveActivity($app_id, $activity_id, $subscription_id)
```

Stop Live Activity

Stops a Live Activity

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The OneSignal App ID for your app.  Available in Keys & IDs.
$activity_id = 'activity_id_example'; // string | Live Activity record ID
$subscription_id = 'subscription_id_example'; // string | Subscription ID

try {
    $apiInstance->endLiveActivity($app_id, $activity_id, $subscription_id);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->endLiveActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The OneSignal App ID for your app.  Available in Keys &amp; IDs. |
 **activity_id** | **string**| Live Activity record ID |
 **subscription_id** | **string**| Subscription ID |

### Return type

void (empty response body)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `exportEvents()`

```php
exportEvents($notification_id, $app_id): \onesignal\client\model\ExportEventsSuccessResponse
```

Export CSV of Events

Generate a compressed CSV report of all of the events data for a notification. This will return a URL immediately upon success but it may take several minutes for the CSV to become available at that URL depending on the volume of data. Only one export can be in-progress per OneSignal account at any given time.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$notification_id = 'notification_id_example'; // string | The ID of the notification to export events from.
$app_id = 'app_id_example'; // string | The ID of the app that the notification belongs to.

try {
    $result = $apiInstance->exportEvents($notification_id, $app_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->exportEvents: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **notification_id** | **string**| The ID of the notification to export events from. |
 **app_id** | **string**| The ID of the app that the notification belongs to. |

### Return type

[**\onesignal\client\model\ExportEventsSuccessResponse**](../Model/ExportEventsSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `exportPlayers()`

```php
exportPlayers($app_id, $export_players_request_body): \onesignal\client\model\ExportPlayersSuccessResponse
```

Export CSV of Players

Generate a compressed CSV export of all of your current user data This method can be used to generate a compressed CSV export of all of your current user data. It is a much faster alternative than retrieving this data using the /players API endpoint. The file will be compressed using GZip. The file may take several minutes to generate depending on the number of users in your app. The URL generated will be available for 3 days and includes random v4 uuid as part of the resource name to be unguessable. &#x1F6A7; 403 Error Responses          You can test if it is complete by making a GET request to the csv_file_url value. This file may take time to generate depending on how many device records are being pulled. If the file is not ready, a 403 error will be returned. Otherwise the file itself will be returned. &#x1F6A7; Requires Authentication Key Requires your OneSignal App's REST API Key, available in Keys & IDs. &#x1F6A7; Concurrent Exports Only one concurrent export is allowed per OneSignal account. Please ensure you have successfully downloaded the .csv.gz file before exporting another app. CSV File Format: - Default Columns:   | Field | Details |   | --- | --- |   | id | OneSignal Player Id |   | identifier | Push Token |   | session_count | Number of times they visited the app or site   | language | Device language code |   | timezone | Number of seconds away from UTC. Example: -28800 |   | game_version | Version of your mobile app gathered from Android Studio versionCode in your App/build.gradle and iOS uses kCFBundleVersionKey in Xcode. |   | device_os | Device Operating System Version. Example: 80 = Chrome 80, 9 = Android 9 |   | device_type | Device Operating System Type |   | device_model | Device Hardware String Code. Example: Mobile Web Subscribers will have `Linux armv` |   | ad_id | Based on the Google Advertising Id for Android, identifierForVendor for iOS. OptedOut means user turned off Advertising tracking on the device. |   | tags | Current OneSignal Data Tags on the device. |   | last_active | Date and time the user last opened the mobile app or visited the site. |   | playtime | Total amount of time in seconds the user had the mobile app open. |   | amount_spent |  Mobile only - amount spent in USD on In-App Purchases. |    | created_at | Date and time the device record was created in OneSignal. Mobile - first time they opened the app with OneSignal SDK. Web - first time the user subscribed to the site. |   | invalid_identifier | t = unsubscribed, f = subscibed |   | badge_count | Current number of badges on the device | - Extra Columns:   | Field | Details |   | --- | --- |   | external_user_id | Your User Id set on the device |   | notification_types | Notification types |   | location | Location points (Latitude and Longitude) set on the device. |   | country | Country code |   | rooted | Android device rooted or not |   | ip | IP Address of the device if being tracked. See Handling Personal Data. |   | web_auth | Web Only authorization key. |   | web_p256 | Web Only p256 key. |

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The app ID that you want to export devices from
$export_players_request_body = new \onesignal\client\model\ExportPlayersRequestBody(); // \onesignal\client\model\ExportPlayersRequestBody

try {
    $result = $apiInstance->exportPlayers($app_id, $export_players_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->exportPlayers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The app ID that you want to export devices from |
 **export_players_request_body** | [**\onesignal\client\model\ExportPlayersRequestBody**](../Model/ExportPlayersRequestBody.md)|  | [optional]

### Return type

[**\onesignal\client\model\ExportPlayersSuccessResponse**](../Model/ExportPlayersSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `fetchAliases()`

```php
fetchAliases($app_id, $subscription_id): \onesignal\client\model\UserIdentityResponse
```



Lists all Aliases for the User identified by :subscription_id.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$subscription_id = 'subscription_id_example'; // string

try {
    $result = $apiInstance->fetchAliases($app_id, $subscription_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->fetchAliases: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **subscription_id** | **string**|  |

### Return type

[**\onesignal\client\model\UserIdentityResponse**](../Model/UserIdentityResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `fetchUser()`

```php
fetchUser($app_id, $alias_label, $alias_id): \onesignal\client\model\User
```



Returns the User’s properties, Aliases, and Subscriptions.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$alias_label = 'alias_label_example'; // string
$alias_id = 'alias_id_example'; // string

try {
    $result = $apiInstance->fetchUser($app_id, $alias_label, $alias_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->fetchUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **alias_label** | **string**|  |
 **alias_id** | **string**|  |

### Return type

[**\onesignal\client\model\User**](../Model/User.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `fetchUserIdentity()`

```php
fetchUserIdentity($app_id, $alias_label, $alias_id): \onesignal\client\model\InlineResponse200
```



Lists all Aliases for the User identified by (:alias_label, :alias_id).

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$alias_label = 'alias_label_example'; // string
$alias_id = 'alias_id_example'; // string

try {
    $result = $apiInstance->fetchUserIdentity($app_id, $alias_label, $alias_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->fetchUserIdentity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **alias_label** | **string**|  |
 **alias_id** | **string**|  |

### Return type

[**\onesignal\client\model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getApp()`

```php
getApp($app_id): \onesignal\client\model\App
```

View an app

View the details of a single OneSignal app

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: user_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | An app id

try {
    $result = $apiInstance->getApp($app_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getApp: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| An app id |

### Return type

[**\onesignal\client\model\App**](../Model/App.md)

### Authorization

[user_key](../../README.md#user_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getApps()`

```php
getApps(): \onesignal\client\model\App[]
```

View apps

View the details of all of your current OneSignal apps

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: user_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getApps();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getApps: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\onesignal\client\model\App[]**](../Model/App.md)

### Authorization

[user_key](../../README.md#user_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getEligibleIams()`

```php
getEligibleIams($app_id, $subscription_id): \onesignal\client\model\InlineResponse2003
```



Manifest of In-App Messages the Subscription is eligible to display by the SDK.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$subscription_id = 'subscription_id_example'; // string

try {
    $result = $apiInstance->getEligibleIams($app_id, $subscription_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getEligibleIams: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **subscription_id** | **string**|  |

### Return type

[**\onesignal\client\model\InlineResponse2003**](../Model/InlineResponse2003.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getNotification()`

```php
getNotification($app_id, $notification_id): \onesignal\client\model\NotificationWithMeta
```

View notification

View the details of a single notification and outcomes associated with it

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$notification_id = 'notification_id_example'; // string

try {
    $result = $apiInstance->getNotification($app_id, $notification_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getNotification: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **notification_id** | **string**|  |

### Return type

[**\onesignal\client\model\NotificationWithMeta**](../Model/NotificationWithMeta.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getNotificationHistory()`

```php
getNotificationHistory($notification_id, $get_notification_request_body): \onesignal\client\model\NotificationHistorySuccessResponse
```

Notification History

-> View the devices sent a message - OneSignal Paid Plan Required This method will return all devices that were sent the given notification_id of an Email or Push Notification if used within 7 days of the date sent. After 7 days of the sending date, the message history data will be unavailable. After a successful response is received, the destination url may be polled until the file becomes available. Most exports are done in ~1-3 minutes, so setting a poll interval of 10 seconds should be adequate. For use cases that are not meant to be consumed by a script, an email will be sent to the supplied email address. &#x1F6A7; Requirements A OneSignal Paid Plan. Turn on Send History via OneSignal API in Settings -> Analytics. Cannot get data before this was turned on. Must be called within 7 days after sending the message. Messages targeting under 1000 recipients will not have \"sent\" events recorded, but will show \"clicked\" events. Requires your OneSignal App's REST API Key, available in Keys & IDs.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$notification_id = 'notification_id_example'; // string | The \"id\" of the message found in the Notification object
$get_notification_request_body = new \onesignal\client\model\GetNotificationRequestBody(); // \onesignal\client\model\GetNotificationRequestBody

try {
    $result = $apiInstance->getNotificationHistory($notification_id, $get_notification_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getNotificationHistory: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **notification_id** | **string**| The \&quot;id\&quot; of the message found in the Notification object |
 **get_notification_request_body** | [**\onesignal\client\model\GetNotificationRequestBody**](../Model/GetNotificationRequestBody.md)|  |

### Return type

[**\onesignal\client\model\NotificationHistorySuccessResponse**](../Model/NotificationHistorySuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getNotifications()`

```php
getNotifications($app_id, $limit, $offset, $kind): \onesignal\client\model\NotificationSlice
```

View notifications

View the details of multiple notifications

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The app ID that you want to view notifications from
$limit = 56; // int | How many notifications to return.  Max is 50.  Default is 50.
$offset = 56; // int | Page offset.  Default is 0.  Results are sorted by queued_at in descending order.  queued_at is a representation of the time that the notification was queued at.
$kind = 56; // int | Kind of notifications returned:   * unset - All notification types (default)   * `0` - Dashboard only   * `1` - API only   * `3` - Automated only

try {
    $result = $apiInstance->getNotifications($app_id, $limit, $offset, $kind);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getNotifications: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The app ID that you want to view notifications from |
 **limit** | **int**| How many notifications to return.  Max is 50.  Default is 50. | [optional]
 **offset** | **int**| Page offset.  Default is 0.  Results are sorted by queued_at in descending order.  queued_at is a representation of the time that the notification was queued at. | [optional]
 **kind** | **int**| Kind of notifications returned:   * unset - All notification types (default)   * &#x60;0&#x60; - Dashboard only   * &#x60;1&#x60; - API only   * &#x60;3&#x60; - Automated only | [optional]

### Return type

[**\onesignal\client\model\NotificationSlice**](../Model/NotificationSlice.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getOutcomes()`

```php
getOutcomes($app_id, $outcome_names, $outcome_names2, $outcome_time_range, $outcome_platforms, $outcome_attribution): \onesignal\client\model\OutcomesData
```

View Outcomes

View the details of all the outcomes associated with your app  &#x1F6A7; Requires Authentication Key Requires your OneSignal App's REST API Key, available in Keys & IDs.  &#x1F6A7; Outcome Data Limitations Outcomes are only accessible for around 30 days before deleted from our servers. You will need to export this data every month if you want to keep it.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The OneSignal App ID for your app.  Available in Keys & IDs.
$outcome_names = 'outcome_names_example'; // string | Required Comma-separated list of names and the value (sum/count) for the returned outcome data. Note: Clicks only support count aggregation. For out-of-the-box OneSignal outcomes such as click and session duration, please use the \"os\" prefix with two underscores. For other outcomes, please use the name specified by the user. Example:os__session_duration.count,os__click.count,CustomOutcomeName.sum
$outcome_names2 = 'outcome_names_example'; // string | Optional If outcome names contain any commas, then please specify only one value at a time. Example: outcome_names[]=os__click.count&outcome_names[]=Sales, Purchase.count where \"Sales, Purchase\" is the custom outcomes with a comma in the name.
$outcome_time_range = 'outcome_time_range_example'; // string | Optional Time range for the returned data. The values can be 1h (for the last 1 hour data), 1d (for the last 1 day data), or 1mo (for the last 1 month data). Default is 1h if the parameter is omitted.
$outcome_platforms = 'outcome_platforms_example'; // string | Optional Platform id. Refer device's platform ids for values. Example: outcome_platform=0 for iOS outcome_platform=7,8 for Safari and Firefox Default is data from all platforms if the parameter is omitted.
$outcome_attribution = 'outcome_attribution_example'; // string | Optional Attribution type for the outcomes. The values can be direct or influenced or unattributed. Example: outcome_attribution=direct Default is total (returns direct+influenced+unattributed) if the parameter is omitted.

try {
    $result = $apiInstance->getOutcomes($app_id, $outcome_names, $outcome_names2, $outcome_time_range, $outcome_platforms, $outcome_attribution);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getOutcomes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The OneSignal App ID for your app.  Available in Keys &amp; IDs. |
 **outcome_names** | **string**| Required Comma-separated list of names and the value (sum/count) for the returned outcome data. Note: Clicks only support count aggregation. For out-of-the-box OneSignal outcomes such as click and session duration, please use the \&quot;os\&quot; prefix with two underscores. For other outcomes, please use the name specified by the user. Example:os__session_duration.count,os__click.count,CustomOutcomeName.sum |
 **outcome_names2** | **string**| Optional If outcome names contain any commas, then please specify only one value at a time. Example: outcome_names[]&#x3D;os__click.count&amp;outcome_names[]&#x3D;Sales, Purchase.count where \&quot;Sales, Purchase\&quot; is the custom outcomes with a comma in the name. | [optional]
 **outcome_time_range** | **string**| Optional Time range for the returned data. The values can be 1h (for the last 1 hour data), 1d (for the last 1 day data), or 1mo (for the last 1 month data). Default is 1h if the parameter is omitted. | [optional]
 **outcome_platforms** | **string**| Optional Platform id. Refer device&#39;s platform ids for values. Example: outcome_platform&#x3D;0 for iOS outcome_platform&#x3D;7,8 for Safari and Firefox Default is data from all platforms if the parameter is omitted. | [optional]
 **outcome_attribution** | **string**| Optional Attribution type for the outcomes. The values can be direct or influenced or unattributed. Example: outcome_attribution&#x3D;direct Default is total (returns direct+influenced+unattributed) if the parameter is omitted. | [optional]

### Return type

[**\onesignal\client\model\OutcomesData**](../Model/OutcomesData.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getPlayer()`

```php
getPlayer($app_id, $player_id, $email_auth_hash): \onesignal\client\model\Player
```

View device

View the details of an existing device in one of your OneSignal apps

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | Your app_id for this device
$player_id = 'player_id_example'; // string | Player's OneSignal ID
$email_auth_hash = 'email_auth_hash_example'; // string | Email - Only required if you have enabled Identity Verification and device_type is email (11).

try {
    $result = $apiInstance->getPlayer($app_id, $player_id, $email_auth_hash);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getPlayer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| Your app_id for this device |
 **player_id** | **string**| Player&#39;s OneSignal ID |
 **email_auth_hash** | **string**| Email - Only required if you have enabled Identity Verification and device_type is email (11). | [optional]

### Return type

[**\onesignal\client\model\Player**](../Model/Player.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getPlayers()`

```php
getPlayers($app_id, $limit, $offset): \onesignal\client\model\PlayerSlice
```

View devices

View the details of multiple devices in one of your OneSignal apps Unavailable for Apps Over 80,000 Users For performance reasons, this method is not available for larger apps. Larger apps should use the CSV export API endpoint, which is much more performant.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The app ID that you want to view players from
$limit = 56; // int | How many devices to return. Max is 300. Default is 300
$offset = 56; // int | Result offset. Default is 0. Results are sorted by id;

try {
    $result = $apiInstance->getPlayers($app_id, $limit, $offset);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->getPlayers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The app ID that you want to view players from |
 **limit** | **int**| How many devices to return. Max is 300. Default is 300 | [optional]
 **offset** | **int**| Result offset. Default is 0. Results are sorted by id; | [optional]

### Return type

[**\onesignal\client\model\PlayerSlice**](../Model/PlayerSlice.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `identifyUserByAlias()`

```php
identifyUserByAlias($app_id, $alias_label, $alias_id, $user_identity_request_body): \onesignal\client\model\InlineResponse200
```



Upserts one or more Aliases to an existing User identified by (:alias_label, :alias_id).

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$alias_label = 'alias_label_example'; // string
$alias_id = 'alias_id_example'; // string
$user_identity_request_body = new \onesignal\client\model\UserIdentityRequestBody(); // \onesignal\client\model\UserIdentityRequestBody

try {
    $result = $apiInstance->identifyUserByAlias($app_id, $alias_label, $alias_id, $user_identity_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->identifyUserByAlias: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **alias_label** | **string**|  |
 **alias_id** | **string**|  |
 **user_identity_request_body** | [**\onesignal\client\model\UserIdentityRequestBody**](../Model/UserIdentityRequestBody.md)|  |

### Return type

[**\onesignal\client\model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `identifyUserBySubscriptionId()`

```php
identifyUserBySubscriptionId($app_id, $subscription_id, $user_identity_request_body): \onesignal\client\model\UserIdentityResponse
```



Upserts one or more Aliases for the User identified by :subscription_id.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$subscription_id = 'subscription_id_example'; // string
$user_identity_request_body = new \onesignal\client\model\UserIdentityRequestBody(); // \onesignal\client\model\UserIdentityRequestBody

try {
    $result = $apiInstance->identifyUserBySubscriptionId($app_id, $subscription_id, $user_identity_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->identifyUserBySubscriptionId: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **subscription_id** | **string**|  |
 **user_identity_request_body** | [**\onesignal\client\model\UserIdentityRequestBody**](../Model/UserIdentityRequestBody.md)|  |

### Return type

[**\onesignal\client\model\UserIdentityResponse**](../Model/UserIdentityResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `transferSubscription()`

```php
transferSubscription($app_id, $subscription_id, $transfer_subscription_request_body): \onesignal\client\model\UserIdentityResponse
```



Transfers this Subscription to the User identified by the identity in the payload.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$subscription_id = 'subscription_id_example'; // string
$transfer_subscription_request_body = new \onesignal\client\model\TransferSubscriptionRequestBody(); // \onesignal\client\model\TransferSubscriptionRequestBody

try {
    $result = $apiInstance->transferSubscription($app_id, $subscription_id, $transfer_subscription_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->transferSubscription: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **subscription_id** | **string**|  |
 **transfer_subscription_request_body** | [**\onesignal\client\model\TransferSubscriptionRequestBody**](../Model/TransferSubscriptionRequestBody.md)|  |

### Return type

[**\onesignal\client\model\UserIdentityResponse**](../Model/UserIdentityResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateApp()`

```php
updateApp($app_id, $app): \onesignal\client\model\App
```

Update an app

Updates the name or configuration settings of an existing OneSignal app

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: user_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | An app id
$app = new \onesignal\client\model\App(); // \onesignal\client\model\App

try {
    $result = $apiInstance->updateApp($app_id, $app);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->updateApp: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| An app id |
 **app** | [**\onesignal\client\model\App**](../Model/App.md)|  |

### Return type

[**\onesignal\client\model\App**](../Model/App.md)

### Authorization

[user_key](../../README.md#user_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateLiveActivity()`

```php
updateLiveActivity($app_id, $activity_id, $update_live_activity_request): \onesignal\client\model\UpdateLiveActivitySuccessResponse
```

Update a Live Activity via Push

Updates a specified live activity.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The OneSignal App ID for your app.  Available in Keys & IDs.
$activity_id = 'activity_id_example'; // string | Live Activity record ID
$update_live_activity_request = new \onesignal\client\model\UpdateLiveActivityRequest(); // \onesignal\client\model\UpdateLiveActivityRequest

try {
    $result = $apiInstance->updateLiveActivity($app_id, $activity_id, $update_live_activity_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->updateLiveActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The OneSignal App ID for your app.  Available in Keys &amp; IDs. |
 **activity_id** | **string**| Live Activity record ID |
 **update_live_activity_request** | [**\onesignal\client\model\UpdateLiveActivityRequest**](../Model/UpdateLiveActivityRequest.md)|  |

### Return type

[**\onesignal\client\model\UpdateLiveActivitySuccessResponse**](../Model/UpdateLiveActivitySuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updatePlayer()`

```php
updatePlayer($player_id, $player): \onesignal\client\model\UpdatePlayerSuccessResponse
```

Edit device

Update an existing device in one of your OneSignal apps

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$player_id = 'player_id_example'; // string | Player's OneSignal ID
$player = new \onesignal\client\model\Player(); // \onesignal\client\model\Player

try {
    $result = $apiInstance->updatePlayer($player_id, $player);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->updatePlayer: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **player_id** | **string**| Player&#39;s OneSignal ID |
 **player** | [**\onesignal\client\model\Player**](../Model/Player.md)|  |

### Return type

[**\onesignal\client\model\UpdatePlayerSuccessResponse**](../Model/UpdatePlayerSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updatePlayerTags()`

```php
updatePlayerTags($app_id, $external_user_id, $update_player_tags_request_body): \onesignal\client\model\UpdatePlayerTagsSuccessResponse
```

Edit tags with external user id

Update an existing device's tags in one of your OneSignal apps using the External User ID. Warning - Android SDK Data Synchronization Tags added through the Android SDK tagging methods may not update if using the API to change or update the same tag. For example, if you use SDK method sendTag(\"key\", \"value1\") then update the tag value to \"value2\" with this API endpoint. You will not be able to set the value back to \"value1\" through the SDK, you will need to change it to something different through the SDK to be reset. Recommendations if using this Endpoint on Android Mobile Apps: 1 - Do not use the same tag keys for SDK and API updates 2 - If you want to use the same key for both SDK and API updates, call the SDK getTags method first to update the device's tags. This is only applicable on the Android Mobile App SDKs. &#128216; Deleting Tags To delete a tag, include its key and set its value to blank. Omitting a key/value will not delete it. For example, if I wanted to delete two existing tags rank and category while simultaneously adding a new tag class, the tags JSON would look like the following: \"tags\": {    \"rank\": \"\",    \"category\": \"\",    \"class\": \"my_new_value\" }

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string | The OneSignal App ID the user record is found under.
$external_user_id = 'external_user_id_example'; // string | The External User ID mapped to teh device record in OneSignal.  Must be actively set on the device to be updated.
$update_player_tags_request_body = new \onesignal\client\model\UpdatePlayerTagsRequestBody(); // \onesignal\client\model\UpdatePlayerTagsRequestBody

try {
    $result = $apiInstance->updatePlayerTags($app_id, $external_user_id, $update_player_tags_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->updatePlayerTags: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**| The OneSignal App ID the user record is found under. |
 **external_user_id** | **string**| The External User ID mapped to teh device record in OneSignal.  Must be actively set on the device to be updated. |
 **update_player_tags_request_body** | [**\onesignal\client\model\UpdatePlayerTagsRequestBody**](../Model/UpdatePlayerTagsRequestBody.md)|  | [optional]

### Return type

[**\onesignal\client\model\UpdatePlayerTagsSuccessResponse**](../Model/UpdatePlayerTagsSuccessResponse.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateSubscription()`

```php
updateSubscription($app_id, $subscription_id, $update_subscription_request_body)
```



Updates an existing Subscription’s properties.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$subscription_id = 'subscription_id_example'; // string
$update_subscription_request_body = new \onesignal\client\model\UpdateSubscriptionRequestBody(); // \onesignal\client\model\UpdateSubscriptionRequestBody

try {
    $apiInstance->updateSubscription($app_id, $subscription_id, $update_subscription_request_body);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->updateSubscription: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **subscription_id** | **string**|  |
 **update_subscription_request_body** | [**\onesignal\client\model\UpdateSubscriptionRequestBody**](../Model/UpdateSubscriptionRequestBody.md)|  |

### Return type

void (empty response body)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateUser()`

```php
updateUser($app_id, $alias_label, $alias_id, $update_user_request): \onesignal\client\model\InlineResponse202
```



Updates an existing User’s properties.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: app_key
$config = onesignal\client\Configuration::getDefaultConfiguration()
                                                ->setAppKeyToken('YOUR_APP_KEY_TOKEN')
                                                ->setUserKeyToken('YOUR_USER_KEY_TOKEN');



$apiInstance = new onesignal\client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$app_id = 'app_id_example'; // string
$alias_label = 'alias_label_example'; // string
$alias_id = 'alias_id_example'; // string
$update_user_request = new \onesignal\client\model\UpdateUserRequest(); // \onesignal\client\model\UpdateUserRequest

try {
    $result = $apiInstance->updateUser($app_id, $alias_label, $alias_id, $update_user_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->updateUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **app_id** | **string**|  |
 **alias_label** | **string**|  |
 **alias_id** | **string**|  |
 **update_user_request** | [**\onesignal\client\model\UpdateUserRequest**](../Model/UpdateUserRequest.md)|  |

### Return type

[**\onesignal\client\model\InlineResponse202**](../Model/InlineResponse202.md)

### Authorization

[app_key](../../README.md#app_key)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
