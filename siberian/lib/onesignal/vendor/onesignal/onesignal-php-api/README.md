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

## Live Activities
### Begin Live Activity
```php
$activityId = "activity_id_example";
$beginLiveActivityRequest = new BeginLiveActivityRequest(array(
    'push_token' => "push_token_example",
    'subscription_id' => "player_id_example"
));

self::$apiInstance->beginLiveActivity(APP_ID, $activityId, $beginLiveActivityRequest);
```

### Update Live Activity
```php
$activityId = "activity_id_example";
$updateLiveActivityRequest = new UpdateLiveActivityRequest(array(
    'event' => 'update',
    'name' => 'contents',
    'event_updates' => array('data' => 'test')
));

self::$apiInstance->updateLiveActivity(APP_ID, $activityId, $updateLiveActivityRequest);
```

### End Live Activity
```php
$activityId = "activity_id_example";
$subscriptionId = "player_id_example";
self::$apiInstance->endLiveActivity(APP_ID, $activityId, $subscriptionId);
```

## Users
### Creating a user
```php
// Create user model
$user = new User();
$aliasLabel = '<ALIAS_LABEL>';
$aliasId = '<ALIAS_ID>';
$pushToken = '<DEVICE_PUSH_TOKEN>';

$subscriptionObject = new SubscriptionObject();
$subscriptionObject->setType('iOSPush');
$subscriptionObject->setToken($pushToken);

$user->setIdentity(array($aliasLabel => $aliasId));
$user->setSubscriptions([$subscriptionObject]);

// Send model to API
$createUserResponse = self::$apiInstance->createUser(APP_ID, $user);
```

### Fetch user by an alias
```php
$fetchUserResponse = self::$apiInstance->fetchUser(APP_ID, $aliasLabel, $aliasId);
```

### Update user
```php
$updateUserRequest = new UpdateUserRequest(array(
    'properties' => array(
        'language' => 'fr'
    ))
);

$updateUserResponse = self::$apiInstance->updateUser(APP_ID, $aliasLabel, $aliasId, $updateUserRequest);
```

### Delete user
```php
self::$apiInstance->deleteUser(APP_ID, $aliasLabel, $aliasId);
```

### Create subscription
```php
$createSubscriptionRequestBody = new CreateSubscriptionRequestBody();
$subscriptionObject = new SubscriptionObject();
$subscriptionObject->setType('AndroidPush');
$subscriptionObject->setToken('DEVICE_PUSH_TOKEN');
$createSubscriptionRequestBody->setSubscription($subscriptionObject);

$createSubscriptionResponse =
    self::$apiInstance->createSubscription(APP_ID, $aliasLabel, $aliasId, $createSubscriptionRequestBody);
```

### Update subscription
```php
$updateSubscriptionRequestBody = new UpdateSubscriptionRequestBody();
$subscriptionObject = new SubscriptionObject();
$subscriptionObject->setType('AndroidPush');
$subscriptionObject->setToken('DEVICE_PUSH_TOKEN'
$updateSubscriptionRequestBody->setSubscription($subscriptionObject);

self::$apiInstance->updateSubscription(APP_ID, $subscriptionId, $updateSubscriptionRequestBody);
```

### Delete subscription
```php
self::$apiInstance->deleteSubscription(APP_ID, '<SUBSCRIPTION_ID>');
```
### Delete Alias
```php
self::$apiInstance->deleteAlias(APP_ID, '<ALIAS_LABEL>', '<ALIAS_ID>', '<ALIAS_LABEL_TO_DELETE>');
```

### Fetch aliases by subscription id
```php
$fetchAliasesResponse = self::$apiInstance->fetchAliases(APP_ID, '<SUBSCRIPTION_ID>');
```

### Fetch aliases by another alias
```php
$fetchAliasesResponse = self::$apiInstance->fetchUserIdentity(APP_ID, '<ALIAS_LABEL>', '<ALIAS_ID>');
```

### Identify user by subscription id
```php
$userIdentityRequestBody = new UserIdentityRequestBody();

$userIdentityRequestBody->setIdentity(array(
    '<NEW_ALIAS_LABEL>' => '<NEW_ALIAS_ID>'
));

// Act
$fetchAliasesResponse = self::$apiInstance->identifyUserBySubscriptionId(
    APP_ID, '<SUBSCRIPTION_ID>', $userIdentityRequestBody);
```

### Identify user by another alias
```php
$userIdentityRequestBody = new UserIdentityRequestBody();

$userIdentityRequestBody->setIdentity(array(
    '<NEW_ALIAS_LABEL>' => '<NEW_ALIAS_ID>'
));

// Act
$fetchAliasesResponse = self::$apiInstance->identifyUserByAlias(
    APP_ID, '<ALIAS_LABEL>', '<ALIAS_ID>', $userIdentityRequestBody);
```


### Transfer subscription to another user
```php
$transferSubscriptionRequestBody = new TransferSubscriptionRequestBody();
$transferSubscriptionRequestBody->setIdentity(array('<USER_FROM_ALIAS_LABEL>' => '<USER_FROM_ALIAS_ID>'));

// Act
$transferSubscriptionResponse = self::$apiInstance->transferSubscription(
    APP_ID, '<USER_TO_SUBSCRIPTION_ID>', $transferSubscriptionRequestBody);
```

### Fetch in app messages
```php
$getEligibleIamsResponse = self::$apiInstance->getEligibleIams(APP_ID, '<SUBSCRIPTION_ID>');
```

## API Endpoints

All URIs are relative to *https://onesignal.com/api/v1*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*DefaultApi* | [**beginLiveActivity**](docs/Api/DefaultApi.md#beginliveactivity) | **POST** /apps/{app_id}/live_activities/{activity_id}/token | Start Live Activity
*DefaultApi* | [**cancelNotification**](docs/Api/DefaultApi.md#cancelnotification) | **DELETE** /notifications/{notification_id} | Stop a scheduled or currently outgoing notification
*DefaultApi* | [**createApp**](docs/Api/DefaultApi.md#createapp) | **POST** /apps | Create an app
*DefaultApi* | [**createNotification**](docs/Api/DefaultApi.md#createnotification) | **POST** /notifications | Create notification
*DefaultApi* | [**createPlayer**](docs/Api/DefaultApi.md#createplayer) | **POST** /players | Add a device
*DefaultApi* | [**createSegments**](docs/Api/DefaultApi.md#createsegments) | **POST** /apps/{app_id}/segments | Create Segments
*DefaultApi* | [**createSubscription**](docs/Api/DefaultApi.md#createsubscription) | **POST** /apps/{app_id}/users/by/{alias_label}/{alias_id}/subscriptions | 
*DefaultApi* | [**createUser**](docs/Api/DefaultApi.md#createuser) | **POST** /apps/{app_id}/users | 
*DefaultApi* | [**deleteAlias**](docs/Api/DefaultApi.md#deletealias) | **DELETE** /apps/{app_id}/users/by/{alias_label}/{alias_id}/identity/{alias_label_to_delete} | 
*DefaultApi* | [**deletePlayer**](docs/Api/DefaultApi.md#deleteplayer) | **DELETE** /players/{player_id} | Delete a user record
*DefaultApi* | [**deleteSegments**](docs/Api/DefaultApi.md#deletesegments) | **DELETE** /apps/{app_id}/segments/{segment_id} | Delete Segments
*DefaultApi* | [**deleteSubscription**](docs/Api/DefaultApi.md#deletesubscription) | **DELETE** /apps/{app_id}/subscriptions/{subscription_id} | 
*DefaultApi* | [**deleteUser**](docs/Api/DefaultApi.md#deleteuser) | **DELETE** /apps/{app_id}/users/by/{alias_label}/{alias_id} | 
*DefaultApi* | [**endLiveActivity**](docs/Api/DefaultApi.md#endliveactivity) | **DELETE** /apps/{app_id}/live_activities/{activity_id}/token/{subscription_id} | Stop Live Activity
*DefaultApi* | [**exportEvents**](docs/Api/DefaultApi.md#exportevents) | **POST** /notifications/{notification_id}/export_events?app_id&#x3D;{app_id} | Export CSV of Events
*DefaultApi* | [**exportPlayers**](docs/Api/DefaultApi.md#exportplayers) | **POST** /players/csv_export?app_id&#x3D;{app_id} | Export CSV of Players
*DefaultApi* | [**fetchAliases**](docs/Api/DefaultApi.md#fetchaliases) | **GET** /apps/{app_id}/subscriptions/{subscription_id}/user/identity | 
*DefaultApi* | [**fetchUser**](docs/Api/DefaultApi.md#fetchuser) | **GET** /apps/{app_id}/users/by/{alias_label}/{alias_id} | 
*DefaultApi* | [**fetchUserIdentity**](docs/Api/DefaultApi.md#fetchuseridentity) | **GET** /apps/{app_id}/users/by/{alias_label}/{alias_id}/identity | 
*DefaultApi* | [**getApp**](docs/Api/DefaultApi.md#getapp) | **GET** /apps/{app_id} | View an app
*DefaultApi* | [**getApps**](docs/Api/DefaultApi.md#getapps) | **GET** /apps | View apps
*DefaultApi* | [**getEligibleIams**](docs/Api/DefaultApi.md#geteligibleiams) | **GET** /apps/{app_id}/subscriptions/{subscription_id}/iams | 
*DefaultApi* | [**getNotification**](docs/Api/DefaultApi.md#getnotification) | **GET** /notifications/{notification_id} | View notification
*DefaultApi* | [**getNotificationHistory**](docs/Api/DefaultApi.md#getnotificationhistory) | **POST** /notifications/{notification_id}/history | Notification History
*DefaultApi* | [**getNotifications**](docs/Api/DefaultApi.md#getnotifications) | **GET** /notifications | View notifications
*DefaultApi* | [**getOutcomes**](docs/Api/DefaultApi.md#getoutcomes) | **GET** /apps/{app_id}/outcomes | View Outcomes
*DefaultApi* | [**getPlayer**](docs/Api/DefaultApi.md#getplayer) | **GET** /players/{player_id} | View device
*DefaultApi* | [**getPlayers**](docs/Api/DefaultApi.md#getplayers) | **GET** /players | View devices
*DefaultApi* | [**identifyUserByAlias**](docs/Api/DefaultApi.md#identifyuserbyalias) | **PATCH** /apps/{app_id}/users/by/{alias_label}/{alias_id}/identity | 
*DefaultApi* | [**identifyUserBySubscriptionId**](docs/Api/DefaultApi.md#identifyuserbysubscriptionid) | **PATCH** /apps/{app_id}/subscriptions/{subscription_id}/user/identity | 
*DefaultApi* | [**transferSubscription**](docs/Api/DefaultApi.md#transfersubscription) | **PATCH** /apps/{app_id}/subscriptions/{subscription_id}/owner | 
*DefaultApi* | [**updateApp**](docs/Api/DefaultApi.md#updateapp) | **PUT** /apps/{app_id} | Update an app
*DefaultApi* | [**updateLiveActivity**](docs/Api/DefaultApi.md#updateliveactivity) | **POST** /apps/{app_id}/live_activities/{activity_id}/notifications | Update a Live Activity via Push
*DefaultApi* | [**updatePlayer**](docs/Api/DefaultApi.md#updateplayer) | **PUT** /players/{player_id} | Edit device
*DefaultApi* | [**updatePlayerTags**](docs/Api/DefaultApi.md#updateplayertags) | **PUT** /apps/{app_id}/users/{external_user_id} | Edit tags with external user id
*DefaultApi* | [**updateSubscription**](docs/Api/DefaultApi.md#updatesubscription) | **PATCH** /apps/{app_id}/subscriptions/{subscription_id} | 
*DefaultApi* | [**updateUser**](docs/Api/DefaultApi.md#updateuser) | **PATCH** /apps/{app_id}/users/by/{alias_label}/{alias_id} | 

## Models

- [App](docs/Model/App.md)
- [BasicNotification](docs/Model/BasicNotification.md)
- [BasicNotificationAllOf](docs/Model/BasicNotificationAllOf.md)
- [BasicNotificationAllOfAndroidBackgroundLayout](docs/Model/BasicNotificationAllOfAndroidBackgroundLayout.md)
- [BeginLiveActivityRequest](docs/Model/BeginLiveActivityRequest.md)
- [Button](docs/Model/Button.md)
- [CancelNotificationSuccessResponse](docs/Model/CancelNotificationSuccessResponse.md)
- [CreateNotificationSuccessResponse](docs/Model/CreateNotificationSuccessResponse.md)
- [CreatePlayerSuccessResponse](docs/Model/CreatePlayerSuccessResponse.md)
- [CreateSegmentConflictResponse](docs/Model/CreateSegmentConflictResponse.md)
- [CreateSegmentSuccessResponse](docs/Model/CreateSegmentSuccessResponse.md)
- [CreateSubscriptionRequestBody](docs/Model/CreateSubscriptionRequestBody.md)
- [CreateUserConflictResponse](docs/Model/CreateUserConflictResponse.md)
- [CreateUserConflictResponseErrorsInner](docs/Model/CreateUserConflictResponseErrorsInner.md)
- [CreateUserConflictResponseErrorsItemsMeta](docs/Model/CreateUserConflictResponseErrorsItemsMeta.md)
- [DeletePlayerNotFoundResponse](docs/Model/DeletePlayerNotFoundResponse.md)
- [DeletePlayerSuccessResponse](docs/Model/DeletePlayerSuccessResponse.md)
- [DeleteSegmentNotFoundResponse](docs/Model/DeleteSegmentNotFoundResponse.md)
- [DeleteSegmentSuccessResponse](docs/Model/DeleteSegmentSuccessResponse.md)
- [DeliveryData](docs/Model/DeliveryData.md)
- [ExportEventsSuccessResponse](docs/Model/ExportEventsSuccessResponse.md)
- [ExportPlayersRequestBody](docs/Model/ExportPlayersRequestBody.md)
- [ExportPlayersSuccessResponse](docs/Model/ExportPlayersSuccessResponse.md)
- [Filter](docs/Model/Filter.md)
- [FilterExpressions](docs/Model/FilterExpressions.md)
- [GenericError](docs/Model/GenericError.md)
- [GenericErrorErrorsInner](docs/Model/GenericErrorErrorsInner.md)
- [GetNotificationRequestBody](docs/Model/GetNotificationRequestBody.md)
- [InlineResponse200](docs/Model/InlineResponse200.md)
- [InlineResponse2003](docs/Model/InlineResponse2003.md)
- [InlineResponse201](docs/Model/InlineResponse201.md)
- [InlineResponse202](docs/Model/InlineResponse202.md)
- [InvalidIdentifierError](docs/Model/InvalidIdentifierError.md)
- [Notification](docs/Model/Notification.md)
- [Notification200Errors](docs/Model/Notification200Errors.md)
- [NotificationAllOf](docs/Model/NotificationAllOf.md)
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
- [PlayerNotificationTargetIncludeAliases](docs/Model/PlayerNotificationTargetIncludeAliases.md)
- [PlayerSlice](docs/Model/PlayerSlice.md)
- [PropertiesDeltas](docs/Model/PropertiesDeltas.md)
- [PropertiesObject](docs/Model/PropertiesObject.md)
- [Purchase](docs/Model/Purchase.md)
- [RateLimiterError](docs/Model/RateLimiterError.md)
- [Segment](docs/Model/Segment.md)
- [SegmentNotificationTarget](docs/Model/SegmentNotificationTarget.md)
- [StringMap](docs/Model/StringMap.md)
- [SubscriptionObject](docs/Model/SubscriptionObject.md)
- [TransferSubscriptionRequestBody](docs/Model/TransferSubscriptionRequestBody.md)
- [UpdateLiveActivityRequest](docs/Model/UpdateLiveActivityRequest.md)
- [UpdateLiveActivitySuccessResponse](docs/Model/UpdateLiveActivitySuccessResponse.md)
- [UpdatePlayerSuccessResponse](docs/Model/UpdatePlayerSuccessResponse.md)
- [UpdatePlayerTagsRequestBody](docs/Model/UpdatePlayerTagsRequestBody.md)
- [UpdatePlayerTagsSuccessResponse](docs/Model/UpdatePlayerTagsSuccessResponse.md)
- [UpdateSubscriptionRequestBody](docs/Model/UpdateSubscriptionRequestBody.md)
- [UpdateUserRequest](docs/Model/UpdateUserRequest.md)
- [User](docs/Model/User.md)
- [UserIdentityRequestBody](docs/Model/UserIdentityRequestBody.md)
- [UserIdentityResponse](docs/Model/UserIdentityResponse.md)
- [UserSubscriptionOptions](docs/Model/UserSubscriptionOptions.md)

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


- API version: `1.2.2`
    - Package version: `2.0.2`
