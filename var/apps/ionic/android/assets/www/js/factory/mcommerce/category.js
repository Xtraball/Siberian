/*global
    App
 */
angular.module("starter").factory("McommerceCategory", function($pwaRequest) {

    var factory = {
        value_id: null,
        category_id: null
    };

    factory.findAll = function(offset) {

        if(!this.value_id) {
            return $pwaRequest.reject("[McommerceCategory::findAll] missing value_id.");
        }

        return $pwaRequest.get("mcommerce/mobile_category/findall", {
            urlParams:  {
                value_id: this.value_id,
                category_id: this.category_id,
                offset: offset
            }
        }).then(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
            return data;
        });
    };

    return factory;
});
