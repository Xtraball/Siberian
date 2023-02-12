# # FilterExpressions

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**field** | **string** | Name of the field to use as the first operand in the filter expression. |
**key** | **string** | If &#x60;field&#x60; is &#x60;tag&#x60;, this field is *required* to specify &#x60;key&#x60; inside the tags. | [optional]
**value** | **string** | Constant value to use as the second operand in the filter expression. This value is *required* when the relation operator is a binary operator. | [optional]
**relation** | **string** | Operator of a filter expression. |
**operator** | **string** | Strictly, this must be either &#x60;\&quot;OR\&quot;&#x60;, or &#x60;\&quot;AND\&quot;&#x60;.  It can be used to compose Filters as part of a Filters object. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
