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

}).controller('FolderListController', function($http, $ionicModal, $ionicPopup, $location, $rootScope, $scope, $stateParams, $window, Analytics, Customer, Folder, Url/*, Application, Padlock*/) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Folder.value_id = $stateParams.value_id;
    $scope.search_modal = {};
    $scope.search = {};

    Folder.category_id = $stateParams.category_id;

    Customer.onStatusChange("folder", [
        Url.get("folder/mobile_list/findall", {value_id: Folder.value_id, category_id: Folder.category_id})
    ]);

    $scope.loadContent = function() {

        Folder.findAll().success(function(data) {

            $scope.collection = new Array();

            for(var i = 0; i < data.folders.length; i++) {
                //if(!data.folders[i].is_locked || Customer.can_access_locked_features || Padlock.unlock_by_qrcode) {
                    //if((!Customer.isLoggedIn() && !Padlock.unlock_by_qrcode)  || data.folders[i].code != "padlock") {
                    //    if((Application.handle_code_scan && data.folders[i].code == "code_scan") || data.folders[i].code != "code_scan") {
                            $scope.collection.push(data.folders[i]);
                    //    }
                    //}
                //}
            }

            $scope.cover = data.cover;
            $scope.page_title = data.page_title;

            $scope.search_list = data.search_list;
            $scope.show_search = data.show_search == "1" ? true : false;

        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    };
    
    $scope.goTo = function(feature) {

        if(feature.code == "code_scan") {
        	$window.scan_camera_protocols = JSON.stringify(["tel:", "http:", "https:", "geo:", "ctc:"]);
            Application.openScanCamera({protocols: ["tel:", "http:", "https:", "geo:", "ctc:"]}, function(qrcode) {}, function() {});
        
        } else if(feature.is_link) {
            if($rootScope.isOverview) {
                var popup = $ionicPopup.show({
                    title: $translate.instant("Error"),
                    subTitle: $translate.instant("This feature is available from the application only")
                });
                $timeout(function() {
                    popup.close();
                }, 4000);
                return;
            }
            $window.open(feature.url, $rootScope.getTargetForLink(), "location=no");
        
        } else {
            $location.path(feature.url);
        }

        Analytics.storePageOpening(feature);
    };

    $scope.startSearch = function() {
        if($scope.search.search_value) {
            $ionicModal.fromTemplateUrl('templates/folder/l1/search.html', {
                scope: $scope,
                animation: 'slide-in-up'
            }).then(function(modal) {
                $scope.search_modal = modal;
                $scope.search_modal.show();
            });
        }
    };

    $scope.closeSearch = function() {
        $scope.search.search_value = "";
        $scope.search_modal.hide();
    };

    $scope.$on('$destroy', function() {
        if($scope.search_modal) {
            $scope.search_modal.remove();
        }
    });

    $scope.loadContent();

});