App.service('License', function($http) {
    return {
        getIosBuildLicenseInfo : function(licenseKey) {
            return $http({
                method: 'GET',
                url: "https://extensions.siberiancms.com/?edd_action=check_license&item_name=ios-auto-publish-build-send-your-ios-app-to-itunes-automatically&license="+licenseKey,
                cache: false,
                responseType:'json'
            });
        }
    }
});