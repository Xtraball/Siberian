App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/folder/mobile_list/index/value_id/:value_id", {
        controller: 'FolderListController',
        templateUrl: BASE_URL+"/folder/mobile_list/template",
        code: "folder"
    }).when(BASE_URL+"/folder/mobile_list/index/value_id/:value_id/category_id/:category_id", {
        controller: 'FolderListController',
        templateUrl: BASE_URL+"/folder/mobile_list/template",
        code: "folder"
    });

}).controller('FolderListController', function($scope, $http, $routeParams, $location, Application, Customer, Folder, Padlock, Url) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Folder.value_id = $routeParams.value_id;
    Folder.category_id = $routeParams.category_id;
    $scope.collection = new Array();

    Customer.onStatusChange("folder", [
        Url.get("folder/mobile_list/findall", {value_id: Folder.value_id, category_id: Folder.category_id})
    ]);

    $scope.loadContent = function() {

        Folder.findAll().success(function(data) {

            $scope.collection = new Array();
            for(var i = 0; i < data.folders.length; i++) {
                if(!data.folders[i].is_locked || Customer.can_access_locked_features || Padlock.unlock_by_qrcode) {
                    if((!Customer.isLoggedIn() && !Padlock.unlock_by_qrcode)  || data.folders[i].code != "padlock") {
                        if((Application.handle_code_scan && data.folders[i].code == "code_scan") || data.folders[i].code != "code_scan") {
                            $scope.collection.push(data.folders[i]);
                        }
                    }
                }
            }

            $scope.cover = data.cover;
            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.showItem = function(item) {
        $location.path(item.url);
    };

    $scope.goToUrl = function(option) {
        if(option.code == "code_scan") {
            $window.scan_camera_protocols = JSON.stringify(["tel:", "http:", "https:", "geo:", "ctc:"]);
            Application.openScanCamera({protocols: ["tel:", "http:", "https:", "geo:", "ctc:"]}, function(qrcode) {}, function() {});
        } else {
            $location.path(option.url);
        }
    };

    $scope.loadContent();

});