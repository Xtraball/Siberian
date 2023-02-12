# # Player

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The device&#39;s OneSignal ID | [optional] [readonly]
**invalid_identifier** | **bool** | If true, this is the equivalent of a user being Unsubscribed | [optional] [readonly]
**app_id** | **string** |  | [optional]
**device_type** | **int** | Required The device&#39;s platform:   0 &#x3D; iOS   1 &#x3D; Android   2 &#x3D; Amazon   3 &#x3D; WindowsPhone (MPNS)   4 &#x3D; Chrome Apps / Extensions   5 &#x3D; Chrome Web Push   6 &#x3D; Windows (WNS)   7 &#x3D; Safari   8 &#x3D; Firefox   9 &#x3D; MacOS   10 &#x3D; Alexa   11 &#x3D; Email   13 &#x3D; For Huawei App Gallery Builds SDK Setup. Not for Huawei Devices using FCM   14 &#x3D; SMS |
**external_user_id** | **string** | a custom user ID | [optional]
**external_user_id_auth_hash** | **string** | Only required if you have enabled Identity Verification and device_type is NOT 11 email. | [optional]
**email_auth_hash** | **string** | Email - Only required if you have enabled Identity Verification and device_type is email (11). | [optional]
**identifier** | **string** | Recommended: For Push Notifications, this is the Push Token Identifier from Google or Apple. For Apple Push identifiers, you must strip all non alphanumeric characters. Examples: iOS: 7abcd558f29d0b1f048083e2834ad8ea4b3d87d8ad9c088b33c132706ff445f0 Android: APA91bHbYHk7aq-Uam_2pyJ2qbZvqllyyh2wjfPRaw5gLEX2SUlQBRvOc6sck1sa7H7nGeLNlDco8lXj83HWWwzV... For Email Addresses, set the full email address email@email.com and make sure to set device_type to 11. | [optional]
**language** | **string** | Language code. Typically lower case two letters, except for Chinese where it must be one of zh-Hans or zh-Hant. Example: en | [optional]
**timezone** | **int** | Number of seconds away from UTC. Example: -28800 | [optional]
**game_version** | **string** | Version of your app. Example: 1.1 | [optional]
**device_model** | **string** | Device make and model. Example: iPhone5,1 | [optional]
**device_os** | **string** | Device operating system version. Example: 7.0.4 | [optional]
**ad_id** | **string** | The ad id for the device&#39;s platform: Android &#x3D; Advertising Id iOS &#x3D; identifierForVendor WP8.0 &#x3D; DeviceUniqueId WP8.1 &#x3D; AdvertisingId | [optional]
**sdk** | **string** | Name and version of the sdk/plugin that&#39;s calling this API method (if any) | [optional]
**session_count** | **int** | Number of times the user has played the game, defaults to 1 | [optional]
**tags** | **object** | Custom tags for the player. Only support string and integer key value pairs. Does not support arrays or other nested objects. Setting a tag value to null or an empty string will remove the tag. Example: {\&quot;foo\&quot;:\&quot;bar\&quot;,\&quot;this\&quot;:\&quot;that\&quot;} Limitations: - 100 tags per call - Android SDK users: tags cannot be removed or changed via API if set through SDK sendTag methods. Recommended to only tag devices with 1 kilobyte of data Please consider using your own Database to save more than 1 kilobyte of data. See: Internal Database &amp; CRM | [optional]
**amount_spent** | **float** | Amount the user has spent in USD, up to two decimal places | [optional]
**created_at** | **int** | Unixtime when the player joined the game | [optional]
**playtime** | **int** | Seconds player was running your app. | [optional]
**badge_count** | **int** | Current iOS badge count displayed on the app icon NOTE: Not supported for apps created after June 2018, since badge count for apps created after this date are handled on the client. | [optional]
**last_active** | **int** | Unixtime when the player was last active | [optional]
**notification_types** | **int** | 1 &#x3D; subscribed -2 &#x3D; unsubscribed iOS - These values are set each time the user opens the app from the SDK. Use the SDK function set Subscription instead. Android - You may set this but you can no longer use the SDK method setSubscription later in your app as it will create synchronization issues. | [optional]
**test_type** | **int** | This is used in deciding whether to use your iOS Sandbox or Production push certificate when sending a push when both have been uploaded. Set to the iOS provisioning profile that was used to build your app. 1 &#x3D; Development 2 &#x3D; Ad-Hoc Omit this field for App Store builds. | [optional]
**long** | **float** | Longitude of the device, used for geotagging to segment on. | [optional]
**lat** | **float** | Latitude of the device, used for geotagging to segment on. | [optional]
**country** | **string** | Country code in the ISO 3166-1 Alpha 2 format | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
