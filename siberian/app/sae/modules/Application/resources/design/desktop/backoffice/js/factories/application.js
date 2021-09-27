App.factory('Application', function ($http, Url, DataLoader) {

    var factory = {};

    factory.loadListData = function () {
        return $http({
            method: 'GET',
            url: Url.get("application/backoffice_list/load"),
            cache: true,
            responseType: 'json'
        });
    };

    factory.loadViewData = function () {
        return $http({
            method: 'GET',
            url: Url.get("application/backoffice_view/load"),
            cache: true,
            responseType: 'json'
        });
    };

    factory.loadEditData = function () {
        return $http({
            method: 'GET',
            url: Url.get("application/backoffice_view_acl/load"),
            cache: true,
            responseType: 'json'
        });
    };

    factory.findAll = function () {
        return new DataLoader().sequencedLoading("application/backoffice_list/findall");
    };

    factory.findByAdmin = function (admin_id) {

        return $http({
            method: 'GET',
            url: Url.get("application/backoffice_list/findbyadmin", {admin_id: admin_id}),
            cache: false,
            responseType: 'json'
        });
    };

    factory.find = function (app_id) {

        return $http({
            method: 'GET',
            url: Url.get("application/backoffice_view/find", {app_id: app_id}),
            cache: false,
            responseType: 'json'
        });
    };

    factory.saveInfo = function (application) {

        return $http({
            method: 'POST',
            data: application,
            url: Url.get("application/backoffice_view/save"),
            responseType: 'json'
        });
    };

    factory.switchToIonic = function (app_id) {

        var params = {
            app_id: app_id,
            design_code: "ionic"
        };

        return $http({
            method: 'POST',
            data: params,
            url: Url.get("application/backoffice_view/switchionic"),
            responseType: 'json'
        });
    };

    factory.downloadAndroidApk = function (app_id, design_code) {

        var link = Url.get("application/backoffice_view/downloadsource", {
            device_id: 2,
            app_id: app_id,
            design_code: design_code,
            type: "apk"
        });

        return $http({
            method: 'GET',
            url: link,
            responseType: 'json'
        });
    };

    factory.generateSource = function (device_id, no_ads, app_id, design_code, apk) {

        var link = Url.get(
            "application/backoffice_view/downloadsource", {
                device_id: device_id,
                app_id: app_id,
                design_code: design_code,
                no_ads: no_ads,
                apk: apk,
                type: "zip"
            });

        return $http({
            method: 'GET',
            url: link,
            responseType: 'json'
        });
    };

    factory.cancelQueue = function (application_id, device_id, no_ads, type) {

        var link = Url.get(
            "application/backoffice_view/cancelqueue", {
                device_id: device_id,
                app_id: application_id,
                no_ads: no_ads,
                type: type
            });

        return $http({
            method: 'GET',
            url: link,
            responseType: 'json'
        });
    }

    factory.saveDeviceInfo = function (application) {

        return $http({
            method: 'POST',
            data: application,
            url: Url.get("application/backoffice_view/savedevice"),
            responseType: 'json'
        });
    };

    factory.findAdminAccess = function (params) {
        return $http({
            method: 'POST',
            data: params,
            url: Url.get("application/backoffice_view_acl/findaccess"),
            cache: false,
            responseType: 'json'
        });
    };

    factory.setCanAddPage = function (params) {
        return $http({
            method: 'POST',
            data: params,
            url: Url.get("application/backoffice_view_acl/setaddpage"),
            cache: false,
            responseType: 'json'
        });
    };

    factory.saveAccess = function (params) {
        return $http({
            method: 'POST',
            data: params,
            url: Url.get("application/backoffice_view_acl/saveaccess"),
            cache: false,
            responseType: 'json'
        });
    };

    factory.saveBannerInfo = function (application) {

        return $http({
            method: 'POST',
            data: application,
            url: Url.get("application/backoffice_view/savebanner"),
            responseType: 'json'
        });
    };

    factory.saveAdvertisingInfo = function (application) {

        return $http({
            method: 'POST',
            data: application,
            url: Url.get("application/backoffice_view/saveadvertising"),
            responseType: 'json'
        });
    };

    factory.removeAppAdmin = function (app_id, admin_id) {
        return $http({
            method: 'POST',
            data: {
                app_id: app_id,
                admin_id: admin_id
            },
            url: Url.get('application/backoffice_view/removeadmin'),
            responseType: 'json'
        });
    };

    factory.searchAppOwners = function (filter) {
        return $http({
            method: 'POST',
            data: {
                filter: filter
            },
            url: Url.get('application/backoffice_view/search-owner'),
            responseType: 'json'
        });
    };

    factory.saveAppOwner = function (app_id, owner_id) {
        return $http({
            method: 'POST',
            data: {
                app_id: app_id,
                owner_id: owner_id
            },
            url: Url.get('application/backoffice_view/saveowner'),
            responseType: 'json'
        });
    };
    /** Target MAE/PE */
    factory.deleteApplication = function (appId) {
        return $http({
            method: 'POST',
            data: {
                appId: appId
            },
            url: Url.get('application/backoffice_list/deleteapplication'),
            responseType: 'json'
        });
    };

    /**
     * check the siberian licenceType
     * @returns {*}
     */
    factory.getLicenseType = function () {
        return $http({
            method: 'GET',
            url: Url.get('system/backoffice_config_general/getlicensetype'),
            responseType: 'json'
        });
    };

    return factory;
});

