App.factory('Firewall', function($http, Url) {

    var factory = {};

    factory.findAll = function () {
        return $http({
            method: 'GET',
            url: Url.get("firewall/index/findall"),
            cache: false,
            responseType:'json'
        });
    };

    factory.deleteFwUploadRule = function (value) {
        return $http({
            method: 'POST',
            url: Url.get("firewall/index/deletefwuploadrule"),
            data: {
                value: value
            },
            cache: false,
            responseType:'json'
        });
    };

    factory.addFwUploadRule = function (value) {
        return $http({
            method: 'POST',
            url: Url.get("firewall/index/addfwuploadrule"),
            data: {
                value: value
            },
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
