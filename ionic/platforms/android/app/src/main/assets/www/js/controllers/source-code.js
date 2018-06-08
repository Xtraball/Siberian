/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("SourcecodeViewController", function ($log, $scope, $stateParams, $timeout, SourceCode) {

    angular.extend($scope, {
        is_loading  : true,
        value_id    : $stateParams.value_id
    });

    SourceCode.setValueId($stateParams.value_id);

    $scope.loadContent = function () {

        SourceCode.find()
            .then(function (data) {

                $scope.sourcecode = data.sourcecode;
                $scope.page_title = data.page_title;

                if($scope.sourcecode.code && (typeof $scope.sourcecode.code === "string")) {
                    $scope.is_loading = true;
                    $timeout(function() {
                        try {
                            var iframe = document.querySelector('#sourcecode-iframe[data-value-id="'+$scope.value_id+'"]');
                            iframe = iframe.contentWindow || iframe.contentDocument.document || iframe.contentDocument;
                            iframe.document.open();
                            iframe.document.write($scope.sourcecode.code);
                            iframe.document.close();
                        } catch(e) {
                            $log.error(e);
                        }
                        $scope.is_loading = false;
                    }, 20);
                }

            }, function (data) {
                $log.error("Sourcecode error", data);
            }).then(function() {
                $scope.is_loading = false;
            });
    };

    $scope.loadContent();

});
