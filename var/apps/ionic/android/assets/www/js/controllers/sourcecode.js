App.config(function ($stateProvider) {

    $stateProvider.state('sourcecode-view', {
        url: BASE_PATH + "/sourcecode/mobile_view/index/value_id/:value_id",
        controller: 'SourcecodeViewController',
        templateUrl: 'templates/sourcecode/l1/view.html'
    });

}).controller('SourcecodeViewController', function ($scope, $stateParams, $sce, $timeout, Sourcecode) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    Sourcecode.value_id = $scope.source_id = $stateParams.value_id;

    $scope.loadContent = function () {

        Sourcecode.find().success(function (data) {

            data.sourcecode.htmlFilePath = $sce.trustAsResourceUrl(data.sourcecode.htmlFilePath);
            $scope.sourcecode = data.sourcecode;
            $scope.page_title = data.page_title;

        }).finally(function () {
            $scope.is_loading = false;
            if($scope.sourcecode.htmlFileCode && (typeof $scope.sourcecode.htmlFileCode === "string")) {
                $scope.is_loading = true;
                $timeout(function() {
                    try {
                        var iframe = document.querySelector('#sourcecode-iframe[data-source-id="'+$scope.source_id+'"]');
                        iframe = iframe.contentWindow || iframe.contentDocument.document || iframe.contentDocument;
                        iframe.document.open();
                        iframe.document.write($scope.sourcecode.htmlFileCode);
                        iframe.document.close();
                    } catch(e) {
                        console.error(e);
                    }
                    $scope.is_loading = false;
                }, 300); // give time to ngIf on view side to make iframe appear
            }
        });
    };

    $scope.loadContent();

});
