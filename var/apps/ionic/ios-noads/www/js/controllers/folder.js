App.config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider.state("folder-category-list", {
        url: BASE_PATH+"/folder/mobile_list/index/value_id/:value_id",
        templateUrl: function(param) {
            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch(layout_id) {
                case "2": layout_id = "l2"; break;
                case "3": layout_id = "l3"; break;
                case "4": layout_id = "l4"; break;
                case "1":
                default: layout_id = "l1";
            }
            return 'templates/folder/'+layout_id+'/list.html';
        },
        controller: 'FolderListController',
        cache: false
    }).state("folder-subcategory-list", {
        url: BASE_PATH+"/folder/mobile_list/index/value_id/:value_id/category_id/:category_id",
        controller: "FolderListController",
        templateUrl: function(param) {
            var layout_id = HomepageLayoutProvider.getLayoutIdForValueId(param.value_id);
            switch(layout_id) {
                case "2": layout_id = "l2"; break;
                case "3": layout_id = "l3"; break;
                case "4": layout_id = "l4"; break;
                case "1":
                default: layout_id = "l1";
            }
            return 'templates/folder/'+layout_id+'/list.html';
        }
    })

}).controller('FolderListController', function($sbhttp, $ionicModal, $ionicPopup, $location, $rootScope, $scope, $stateParams, $window, $translate, $timeout, Analytics, AUTH_EVENTS, PADLOCK_EVENTS, Customer, Folder, LinkService, Padlock, Url) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Folder.value_id = $stateParams.value_id;
    $scope.search = {};

    Folder.category_id = $stateParams.category_id;

    Customer.onStatusChange("folder", [
        Url.get("folder/mobile_list/findall", {value_id: Folder.value_id, category_id: Folder.category_id})
    ]);

    function computeCollections() {
        var unlocked = Customer.can_access_locked_features || Padlock.unlocked_by_qrcode;

        function compute(collection) {
            var destination = [];
            angular.forEach(collection, function(folder_item) {
                if((unlocked || !folder_item.is_locked || folder_item.code == "padlock")) {
                    if(unlocked && folder_item.code == "padlock")
                        return;

                    this.push(folder_item);
                }
            }, destination);
            return destination;
        }

        $scope.collection = compute($scope.collection_data);
        $scope.search_list = compute($scope.search_list_data);
    }

    $scope.$on(AUTH_EVENTS.loginStatusChanged, computeCollections);
    $scope.$on(PADLOCK_EVENTS.unlockFeatures, computeCollections);

    $scope.loadContent = function() {
        Folder.findAll().success(function(data) {

            $scope.cover = data.cover;
            $scope.page_title = data.page_title;

            $scope.collection_data = data.folders;
            $scope.search_list_data = data.search_list;
            computeCollections();

            $scope.show_search = data.show_search == "1";

        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.goTo = function(feature) {

        if(feature.code == "code_scan") {
        	$window.scan_camera_protocols = JSON.stringify(["tel:", "http:", "https:", "geo:", "ctc:"]);
            Application.openScanCamera({protocols: ["tel:", "http:", "https:", "geo:", "ctc:"]}, function(qrcode) {}, function() {});
        } else if(feature.offline_mode !== true && $rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }  else if(feature.is_link) {
            var options = {
                "hide_navbar" : (feature.hide_navbar ? true : false),
                "use_external_app" : (feature.use_external_app ? true : false)
            };
            LinkService.openLink(feature.url,options);
        } else {
            $location.path(feature.url);
        }

        Analytics.storePageOpening(feature);
    };

    $scope.loadContent();

});
