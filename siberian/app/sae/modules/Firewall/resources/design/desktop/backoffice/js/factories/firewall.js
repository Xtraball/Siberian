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

    factory.saveFwClamdSettings = function (settings) {
        return $http({
            method: 'POST',
            url: Url.get("firewall/index/savefwclamdsettings"),
            data: {
                fw_clamd_type: settings.type,
                fw_clamd_sock: settings.sock,
                fw_clamd_ip: settings.ip,
                fw_clamd_port: settings.port
            },
            cache: false,
            responseType:'json'
        });
    };

    factory.saveFwSlackSettings = function (settings) {
        return $http({
            method: 'POST',
            url: Url.get("firewall/index/savefwslacksettings"),
            data: {
                fw_slack_is_enabled: settings.is_enabled,
                fw_slack_webhook: settings.webhook,
                fw_slack_channel: settings.channel,
                fw_slack_username: settings.username
            },
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
