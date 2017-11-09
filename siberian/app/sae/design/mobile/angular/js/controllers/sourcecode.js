App.config(function ($routeProvider) {

    $routeProvider.when(BASE_URL + "/sourcecode/mobile_view/index/value_id/:value_id", {
        controller: 'SourcecodeViewController',
        templateUrl: BASE_URL + "/sourcecode/mobile_view/template",
        code: "sourcecode"
    });

}).controller('SourcecodeViewController', function ($scope, $routeParams, Sourcecode) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    Sourcecode.value_id = $routeParams.value_id;

    $scope.loadContent = function () {
        Sourcecode.find().success(function (data) {

            $scope.sourcecode = data.sourcecode;
            $scope.page_title = data.page_title;

        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});