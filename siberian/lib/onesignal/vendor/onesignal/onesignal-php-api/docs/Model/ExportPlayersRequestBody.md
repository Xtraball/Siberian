# # ExportPlayersRequestBody

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**extra_fields** | **string[]** | Additional fields that you wish to include. Currently supports location, country, rooted, notification_types, ip, external_user_id, web_auth, and web_p256. | [optional]
**last_active_since** | **string** | Export all devices with a last_active timestamp greater than this time.  Unixtime in seconds. | [optional]
**segment_name** | **string** | Export all devices belonging to the segment. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
