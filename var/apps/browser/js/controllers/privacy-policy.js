App.config(function($stateProvider) {

    $stateProvider.state('privacy-policy', {
        url: BASE_PATH+"/privacy-policy",
        controller: 'PrivacyPolicyController',
        templateUrl: "templates/cms/privacypolicy/l1/view.html"
    });

}).controller('PrivacyPolicyController', function($scope, $stateParams, $state, Application) {

    $scope.app_name = Application.app_name;

});