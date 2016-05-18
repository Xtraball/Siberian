App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/cms/mobile_privacypolicy", {
        controller: 'PrivacyPolicyController',
        templateUrl: BASE_URL+"/cms/mobile_privacypolicy/template",
        code: "cms"
    });

}).controller('PrivacyPolicyController', function($scope) {

});
