/*global
    angular, lazyLoadResolver, BASE_PATH
 */
angular.module("starter").config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider
        .state("folder-category-list", {
            url             : BASE_PATH + "/folder/mobile_list/index/value_id/:value_id",
            controller      : "FolderListController",
            cache           : false,
            templateUrl     : function(param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch(layout_id) {
                    case 2:
                        layout_id = "l2";
                        break;
                    case 3:
                        layout_id = "l3";
                        break;
                    case 4:
                        layout_id = "l4";
                        break;
                    default:
                        layout_id = "l1";
                }
                return "templates/folder/" + layout_id + "/list.html";
            },
            resolve         : lazyLoadResolver("folder")
        }).state("folder-subcategory-list", {
            url             : BASE_PATH + "/folder/mobile_list/index/value_id/:value_id/category_id/:category_id",
            controller      : "FolderListController",
            cache           : false,
            templateUrl     : function(param) {
                var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
                switch(layout_id) {
                    case 2:
                        layout_id = "l2";
                        break;
                    case 3:
                        layout_id = "l3";
                        break;
                    case 4:
                        layout_id = "l4";
                        break;
                    default:
                        layout_id = "l1";
                }
                return "templates/folder/" + layout_id + "/list.html";
            },
            resolve         : lazyLoadResolver("folder")
        });

});