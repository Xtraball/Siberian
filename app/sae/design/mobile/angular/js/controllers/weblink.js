App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/weblink/mobile_multi/index/value_id/:value_id", {
        controller: 'WeblinkMultiController',
        templateUrl: BASE_URL+"/weblink/mobile_multi/template",
        code: "weblink"
    });

}).controller('WeblinkMultiController', function($window, $scope, $routeParams, Weblink) {

    $scope.weblink = {};
    $scope.is_loading = true;
    $scope.value_id = Weblink.value_id = $routeParams.value_id;

    Weblink.find().success(function(data) {
        $scope.weblink = data.weblink;
        if(!angular.isArray($scope.weblink.links)) {
            $scope.weblink.links = new Array();
        }
        $scope.page_title = data.page_title;
    }).finally(function() {
        $scope.is_loading = false;
    });

    if($scope.isOverview) {

        $window.prepareDummy = function() {
            $scope.dummy = {id: "new"};
            $scope.weblink.links.push($scope.dummy);
            $scope.$apply();
        };

        $window.setAttributeTo = function(id, attribute, value) {

            console.log(id);
            for(var i in $scope.weblink.links) {
                if($scope.weblink.links[i].id == id) {
                    $scope.weblink.links[i][attribute]= value;
                }
            }

            $scope.$apply();
        }

        $window.setCoverUrl = function(url) {
            $scope.weblink.cover_url = url;
            $scope.$apply();
        }

        $scope.$on("$destroy", function() {
            $window.prepareDummy = null;
            $window.setAttributeTo = null;
            $window.setCoverUrl = null;
        });
    }
});