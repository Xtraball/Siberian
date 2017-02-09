App.config(function($stateProvider) {

    $stateProvider.state('links-view', {
        url: BASE_PATH+"/weblink/mobile_multi/index/value_id/:value_id",
        controller: 'LinksViewController',
        templateUrl: "templates/links/l1/view.html",
        code: "weblink"
    });

}).controller('LinksViewController', function($scope, $stateParams, $rootScope, $timeout, $window, Links) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.weblink = {};
    $scope.is_loading = true;
    $scope.value_id = Links.value_id = $stateParams.value_id;

    $scope.loadContent = function() {

        Links.find().success(function(data) {

            $scope.weblink = data.weblink;
            if(!angular.isArray($scope.weblink.links)) {
                $scope.weblink.links = new Array();
            }

            $scope.page_title = data.page_title;

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.openLink = function(url) {

        if($rootScope.isOverview) {
            $rootScope.showMobileFeatureOnlyError();
            return;
        }

        if($rootScope.isOffline) {
            $rootScope.onlineOnly();
            return;
        }

        if(ionic.Platform.isAndroid() && url.indexOf("pdf") >= 0) {
            $window.open(url, "_system", "location=no");
        } else if(ionic.Platform.isIOS() && url.indexOf("pdf") >= 0) {
            $window.open(url, $rootScope.getTargetForLink(), "EnableViewPortScale=yes");
        } else {
            $window.open(url, $rootScope.getTargetForLink(), "location=no");
        }
    };

    if($rootScope.isOverview) {

        $window.prepareDummy = function() {
            $timeout(function() {
                $scope.dummy = {id: "new"};
                $scope.weblink.links.push($scope.dummy);
            });
        };

        $window.setAttributeTo = function(id, attribute, value) {

            $timeout(function() {
                for (var i in $scope.weblink.links) {
                    if ($scope.weblink.links[i].id == id) {
                        $scope.weblink.links[i][attribute] = value;
                    }
                }
            });
        };

        $window.setCoverUrl = function(url) {
            $timeout(function() {
                $scope.weblink.cover_url = url;
            });
        };

    }

    $scope.loadContent();

});
