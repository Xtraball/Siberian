App.config(function($stateProvider) {

    $stateProvider.state('privacy-policy', {
        url: BASE_PATH+"/cms/privacy_policy/index/value_id/:value_id",
        controller: 'PrivacyPolicyController',
        templateUrl: "templates/cms/privacypolicy/l1/privacy-policy.html"
    });

}).controller('PrivacyPolicyController', function($scope, $stateParams, $state, Application, Cms) {

    $scope.value_id = $stateParams.value_id;

    Cms.loadPrivacypolicy($scope.value_id).success(function(data) {
        $scope.page_title = data.page_title;
        $scope.privacy_policy = Application.privacy_policy = data.privacy_policy;
    }).error(function(data) {

    });

    $scope.app_name = Application.app_name;
    $scope.privacy_policy = Application.privacy_policy;

});