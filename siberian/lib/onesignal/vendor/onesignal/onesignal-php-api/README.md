# OneSignal

A powerful way to send personalized messages at scale and build effective customer engagement strategies. Learn more at onesignal.com

For more information, please visit [https://onesignal.com](https://onesignal.com).

## Installation & Usage

### Requirements

PHP 7.3 and later.

### Composer

To install the bindings via [Composer](https://getcomposer.org/), add the following to `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/OneSignal/onesignal-php-api.git"
    }
  ],
  "require": {
    "OneSignal/onesignal-php-api": "*@dev"
  }
}
```

Then run `composer install`

### Manual Installation

Download the files and include `autoload.php`:

```php
<?php
require_once('/path/to/OneSignal/vendor/autoload.php');
```


## Usage examples
### Imports
```php
use DateTime;
use onesignal\client\api\DefaultApi;
use onesignal\client\Configuration;
use onesignal\client\model\GetNotificationRequestBody;
use onesignal\client\model\Notification;
use onesignal\client\model\StringMap;
use onesignal\client\model\Player;
use onesignal\client\model\UpdatePlayerTagsRequestBody;
use onesignal\client\model\ExportPlayersRequestBody;
use onesignal\client\model\Segment;
use onesignal\client\model\FilterExpressions;
use PHPUnit\Framework\TestCase;
use GuzzleHttp;
```

### Constants
```php
const APP_ID = '<YOUR_APP_ID>';
const APP_KEY_TOKEN = '<YOUR_APP_KEY_TOKEN>';
const USER_KEY_TOKEN = '<YOUR_USER_KEY_TOKEN>';
```

### Configure authorization
```php
$config = Configuration::getDefaultConfiguration()
    ->setAppKeyToken(APP_KEY_TOKEN)
    ->setUserKeyToken(USER_KEY_TOKEN);

$apiInstance = new DefaultApi(
    new GuzzleHttp\Client(),
    $config
);
```

## Notifications
### Creating a notification model
```php
function createNotification($enContent): Notification {
    $content = new StringMap();
    $content->setEn($enContent);

    $notification = new Notification();
    $notification->setAppId(APP_ID);
    $notification->setContents($content);
    $notification->setIncludedSegments(['Subscribed Users']);

    return $notification;
}
```

### Sending a notification immediately
```php
$notification = createNotification('PHP Test notification');

$result = $apiInstance->createNotification($notification);
print_r($result);
```

### Scheduling a notification to be sent in 24 hours
```php
$notification = self::createNotification('PHP Test scheduled notification');
$dt = new DateTime();
$dt->modify('+1 day');
$notification->setSendAfter($dt);

$scheduledNotification = $apiInstance->createNotification($notification);
print_r($scheduledNotification);
```

### Sending a notification using Filters
Send this notification only to the users that have not spent any USD on IAP.
```php
$notification = createNotification('PHP Test filtered notification');
$filter1 = new Filter();
$filter1->setField('amount_spent');
$filter1->setRelation('=');
$filter1->setValue('0');
$notification->setFilters([$filter1]);
$result = $apiInstance->createNotification($notification);
print_r($result);
```

### Sending a notification immediately
```php
$notification = createNotification('PHP Test notification');

$result = $apiInstance->createNotification($notification);
print_r($result);
```

### Retrieving a notification
```php
$scheduledNotification = $apiInstance->getNotification(APP_ID, $scheduledNotification->getId());
print_r($scheduledNotification);
```

### Listing notifications by application ID
```php
$getResult = $apiInstance->getNotifications(APP_ID);
print_r($getResult->getNotifications());
```

### Getting notification history
```php
$requestBody = new GetNotificationRequestBody();
$requestBody
    ->setAppId(APP_ID)
    ->setEvents('sent');

$getResult = $apiInstance->getNotificationHistory($scheduledNotification->getId(), $requestBody);
print_r($getResult->getSuccess());
```

## Players
### Creating a new Player model
```php
function createPlayerModel($playerId): Player {
    $player = new Player();

    $player->setAppId(APP_ID);
    $player->setIdentifier($playerId);
    $player->setDeviceType(1);

    return $player;
}
```

### Creating a Player
```php
$player = createPlayerModel('php_test_player_id');
$createPlayerResult = $apiInstance->createPlayer($player);
print_r($createPlayerResult);
```

### Getting a Player
```php
$getPlayerResult = $apiInstance->getPlayer(APP_ID, 'php_test_player_id');
print_r($getPlayerResult);
```

### Getting a list of Players
```php
$limit = 10;
$getPlayersResult = $apiInstance->getPlayers(APP_ID, $limit);
print_r($getPlayersResult->getPlayers());
```

### Deleting a player
```php
$deletePlayerResult = $apiInstance->deletePlayer(APP_ID, 'php_test_player_id');
print_r($deletePlayerResult->getSuccess());
```

### Exporting players into CSV-spreadsheet
```php
$exportPlayersRequestBody = new ExportPlayersRequestBody();
$exportPlayersRequestBody->setExtraFields([]);
$exportPlayersRequestBody->setSegmentName('');

$exportPlayersResult =  $apiInstance->exportPlayers(APP_ID, $exportPlayersRequestBody);
print_r($exportPlayersResult->getCsvFileUrl());
```

## Segments
### Creating a segment
```php
// Settings up the filter. Filters determine a segment.
$filterExpressions = new FilterExpressions();
$filterExpressions->setField('session_count');
$filterExpressions->setRelation('>');
$filterExpressions->setValue('1');
```

### Setting up the segment itself
```php
$segment = new Segment();
$segment->setName('test_segment_name');
$segment->setFilters([$filterExpressions]);

$createSegmentResponse = $apiInstance->createSegments(APP_ID, $segment);
print_r($createSegmentResponse);
```

### Deleting a segment
```php
$deleteSegmentResponse = $apiInstance->deleteSegments(APP_ID, $createSegmentResponse->getId());
print_r($deleteSegmentResponse->getSuccess());
```

## Working with Apps
### Getting an app
```php
$getAppResponse = $apiInstance->getApp(APP_ID);
print_r($getAppResponse);
```

### Getting a list of apps
```php
$getAppsResponse = $apiInstance->getApps();
print_r($getAppsResponse);
```

### Updating an app
```php
$getAppResponse = $apiInstance->getApp(APP_ID);
$getAppResponse->setName('php_test_app_name');
$updateAppResponse = $apiInstance->updateApp(APP_ID, $getAppResponse);
print_r($updateAppResponse);
```

### Outcomes
```php
$outcomeNames = "os__session_duration.count,os__click.count";
$outcomeTimeRange = "1d";
$outcomePlatforms = "5";
$outcomeAttribution = "direct";
$outcomesResponse = $apiInstance->getOutcomes(APP_ID, $outcomeNames, null, $outcomeTimeRange, $outcomePlatforms, $outcomeAttribution);
print_r($outcomesResponse->getOutcomes());
```

## API Endpoints

All URIs are relative to *https://onesignal.com/api/v1*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*DefaultApi* | [**cancelNotification**](docs/Api/DefaultApi.md#cancelnotification) | **DELETE** /notifications/{notification_id} | Stop a scheduled or currently outgoing notification
*DefaultApi* | [**createApp**](docs/Api/DefaultApi.md#createapp) | **POST** /apps | Create an app
*DefaultApi* | [**createNotification**](docs/Api/DefaultApi.md#createnotification) | **POST** /notifications | Create notification
*DefaultApi* | [**createPlayer**](docs/Api/DefaultApi.md#createplayer) | **POST** /players | Add a device
*DefaultApi* | [**createSegments**](docs/Api/DefaultApi.md#createsegments) | **POST** /apps/{app_id}/segments | Create Segments
*DefaultApi* | [**deletePlayer**](docs/Api/DefaultApi.md#deleteplayer) | **DELETE** /players/{player_id} | Delete a user record
*DefaultApi* | [**deleteSegments**](docs/Api/DefaultApi.md#deletesegments) | **DELETE** /apps/{app_id}/segments/{segment_id} | Delete Segments
*DefaultApi* | [**exportPlayers**](docs/Api/DefaultApi.md#exportplayers) | **POST** /players/csv_export?app_id&#x3D;{app_id} | CSV export
*DefaultApi* | [**getApp**](docs/Api/DefaultApi.md#getapp) | **GET** /apps/{app_id} | View an app
*DefaultApi* | [**getApps**](docs/Api/DefaultApi.md#getapps) | **GET** /apps | View apps
*DefaultApi* | [**getNotification**](docs/Api/DefaultApi.md#getnotification) | **GET** /notifications/{notification_id} | View notification
*DefaultApi* | [**getNotificationHistory**](docs/Api/DefaultApi.md#getnotificationhistory) | **POST** /notifications/{notification_id}/history | Notification History
*DefaultApi* | [**getNotifications**](docs/Api/DefaultApi.md#getnotifications) | **GET** /notifications | View notifications
*DefaultApi* | [**getOutcomes**](docs/Api/DefaultApi.md#getoutcomes) | **GET** /apps/{app_id}/outcomes | View Outcomes
*DefaultApi* | [**getPlayer**](docs/Api/DefaultApi.md#getplayer) | **GET** /players/{player_id} | View device
*DefaultApi* | [**getPlayers**](docs/Api/DefaultApi.md#getplayers) | **GET** /players | View devices
*DefaultApi* | [**updateApp**](docs/Api/DefaultApi.md#updateapp) | **PUT** /apps/{app_id} | Update an app
*DefaultApi* | [**updatePlayer**](docs/Api/DefaultApi.md#updateplayer) | **PUT** /players/{player_id} | Edit device
*DefaultApi* | [**updatePlayerTags**](docs/Api/DefaultApi.md#updateplayertags) | **PUT** /apps/{app_id}/users/{external_user_id} | Edit tags with external user id

## Models

- [App](docs/Model/App.md)
- [BasicNotification](docs/Model/BasicNotification.md)
- [BasicNotificationAllOf](docs/Model/BasicNotificationAllOf.md)
- [BasicNotificationAllOfAndroidBackgroundLayout](docs/Model/BasicNotificationAllOfAndroidBackgroundLayout.md)
- [Button](docs/Model/Button.md)
- [CancelNotificationSuccessResponse](docs/Model/CancelNotificationSuccessResponse.md)
- [CreateNotificationBadRequestResponse](docs/Model/CreateNotificationBadRequestResponse.md)
- [CreateNotificationSuccessResponse](docs/Model/CreateNotificationSuccessResponse.md)
- [CreatePlayerSuccessResponse](docs/Model/CreatePlayerSuccessResponse.md)
- [CreateSegmentBadRequestResponse](docs/Model/CreateSegmentBadRequestResponse.md)
- [CreateSegmentConflictResponse](docs/Model/CreateSegmentConflictResponse.md)
- [CreateSegmentSuccessResponse](docs/Model/CreateSegmentSuccessResponse.md)
- [DeletePlayerBadRequestResponse](docs/Model/DeletePlayerBadRequestResponse.md)
- [DeletePlayerNotFoundResponse](docs/Model/DeletePlayerNotFoundResponse.md)
- [DeletePlayerSuccessResponse](docs/Model/DeletePlayerSuccessResponse.md)
- [DeleteSegmentBadRequestResponse](docs/Model/DeleteSegmentBadRequestResponse.md)
- [DeleteSegmentNotFoundResponse](docs/Model/DeleteSegmentNotFoundResponse.md)
- [DeleteSegmentSuccessResponse](docs/Model/DeleteSegmentSuccessResponse.md)
- [DeliveryData](docs/Model/DeliveryData.md)
- [ExportPlayersRequestBody](docs/Model/ExportPlayersRequestBody.md)
- [ExportPlayersSuccessResponse](docs/Model/ExportPlayersSuccessResponse.md)
- [Filter](docs/Model/Filter.md)
- [FilterExpressions](docs/Model/FilterExpressions.md)
- [GetNotificationRequestBody](docs/Model/GetNotificationRequestBody.md)
- [InvalidIdentifierError](docs/Model/InvalidIdentifierError.md)
- [Notification](docs/Model/Notification.md)
- [Notification200Errors](docs/Model/Notification200Errors.md)
- [NotificationAllOf](docs/Model/NotificationAllOf.md)
- [NotificationHistoryBadRequestResponse](docs/Model/NotificationHistoryBadRequestResponse.md)
- [NotificationHistorySuccessResponse](docs/Model/NotificationHistorySuccessResponse.md)
- [NotificationSlice](docs/Model/NotificationSlice.md)
- [NotificationTarget](docs/Model/NotificationTarget.md)
- [NotificationWithMeta](docs/Model/NotificationWithMeta.md)
- [NotificationWithMetaAllOf](docs/Model/NotificationWithMetaAllOf.md)
- [Operator](docs/Model/Operator.md)
- [OutcomeData](docs/Model/OutcomeData.md)
- [OutcomesData](docs/Model/OutcomesData.md)
- [PlatformDeliveryData](docs/Model/PlatformDeliveryData.md)
- [PlatformDeliveryDataEmailAllOf](docs/Model/PlatformDeliveryDataEmailAllOf.md)
- [PlatformDeliveryDataSmsAllOf](docs/Model/PlatformDeliveryDataSmsAllOf.md)
- [Player](docs/Model/Player.md)
- [PlayerNotificationTarget](docs/Model/PlayerNotificationTarget.md)
- [PlayerSlice](docs/Model/PlayerSlice.md)
- [Purchase](docs/Model/Purchase.md)
- [Segment](docs/Model/Segment.md)
- [SegmentNotificationTarget](docs/Model/SegmentNotificationTarget.md)
- [StringMap](docs/Model/StringMap.md)
- [UpdatePlayerSuccessResponse](docs/Model/UpdatePlayerSuccessResponse.md)
- [UpdatePlayerTagsRequestBody](docs/Model/UpdatePlayerTagsRequestBody.md)
- [UpdatePlayerTagsSuccessResponse](docs/Model/UpdatePlayerTagsSuccessResponse.md)

## Authorization
All the OneSignal endpoints require either an *app_key* or *user_key* tokens for authorization. It is recommended to
set up both of those keys during the initial config initialization so that you don't need to worry about which endpoint
requires app_key and which user_key. You can get the value of these keys from your
[app dashboard]([https://app.onesignal.com/apps]) and [user settings](https://app.onesignal.com/profile) pages.



### app_key

- **Type**: Bearer authentication


### user_key

- **Type**: Bearer authentication


## Author

devrel@onesignal.com


- API version: `1.0.1`
    - Package version: `1.0.0`
