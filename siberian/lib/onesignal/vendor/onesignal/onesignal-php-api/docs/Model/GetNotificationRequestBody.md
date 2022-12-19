# # GetNotificationRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**events** | **string** | -&gt; \&quot;sent\&quot; - All the devices by player_id that were sent the specified notification_id.  Notifications targeting under 1000 recipients will not have \&quot;sent\&quot; events recorded, but will show \&quot;clicked\&quot; events. \&quot;clicked\&quot; - All the devices by &#x60;player_id&#x60; that clicked the specified notification_id. | [optional]
**email** | **string** | The email address you would like the report sent. | [optional]
**app_id** | **string** |  | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
