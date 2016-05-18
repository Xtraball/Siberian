App.config(function($stateProvider) {

    $stateProvider.state("folder-category-list", {
        url: BASE_PATH+"/folder/mobile_list/index/value_id/:value_id",
        controller: "FolderListController",
        templateUrl: "templates/folder/l1/list.html"
    }).state("folder-subcategory-list", {
        url: BASE_PATH+"/folder/mobile_list/index/value_id/:value_id/category_id/:category_id",
        controller: "FolderListController",
        templateUrl: "templates/folder/l1/list.html"
    })

}).controller('FolderListController', function($http, $ionicPopup, $location, $rootScope, $scope, $stateParams, $window, Customer, Folder, Url/*, Application, Padlock*/) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Folder.value_id = $stateParams.value_id;
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

    };

    $scope.loadContent();

});