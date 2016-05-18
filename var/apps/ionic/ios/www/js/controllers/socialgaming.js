App.config(function($stateProvider) {

    $stateProvider.state('socialgaming-view', {
        url: BASE_PATH+"/socialgaming/mobile_view/index/value_id/:value_id",
        controller: 'SocialgamingViewController',
        templateUrl: "templates/socialgaming/l1/view.html"
    });

}).controller('SocialgamingViewController', function($scope, $stateParams, Socialgaming) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Socialgaming.value_id = $stateParams.value_id;

    $scope.factory = Socialgaming;
    $scope.collection = new Array();

    $scope.loadContent = function() {
        Socialgaming.findAll().success(function(data) {
            $scope.game = data.game;
            $scope.team_leader = data.team_leader;
            $scope.collection = data.collection;
            $scope.icon_url = data.icon_url;
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});