App.config(function ($stateProvider) {

    $stateProvider.state('sourcecode-view', {
        url: BASE_PATH + "/sourcecode/mobile_view/index/value_id/:value_id",
        controller: 'SourcecodeViewController',
        templateUrl: 'templates/sourcecode/l1/view.html'
    });

}).controller('SourcecodeViewController', function ($scope, $stateParams, $sce, Sourcecode) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    Sourcecode.value_id = $stateParams.value_id;

    $scope.loadContent = function () {

        Sourcecode.find().success(function (data) {

            data.sourcecode.htmlFilePath = $sce.trustAsResourceUrl(data.sourcecode.htmlFilePath);
            $scope.sourcecode = data.sourcecode;
            $scope.page_title = data.page_title;

        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});