App.service('LicenseService', function($http, $q) {
    return {
        getIosBuildLicenseInfo : function(licenseKey) {
            return $q(function(resolv, reject){
                $http({
                    method: 'GET',
                    url: "https://extensions.siberiancms.com/?edd_action=check_license&item_name=ios-auto-publish-build-send-your-ios-app-to-itunes-automatically&license="+licenseKey,
                    cache: false,
                    responseType:'json'
                })
                .then(function(infos) { //succcess
                    if(infos && infos.data && infos.data.success) {
                        var remainingBuild = '';
                        var errorMessage = '';
                        switch (true) {
                            case infos.data.license === "invalid" :
                                errorMessage = 'Invalid license key';
                                break;
                            case infos.data.license === "expired" :
                                errorMessage = 'License key expired';
                                break;
                            case infos.data.license === "item_name_mismatch" :
                                errorMessage = 'This license is not for iOS autopublication';
                                break;
                            case infos.data.activations_left === 0 :
                                errorMessage = 'No more remaining build';
                                break;
                            case infos.data.license === "inactive":
                            case infos.data.license === "valid":
                            case infos.data.license === "site_inactive":
                                if(isFinite(infos.data.activations_left)) {
                                    remainingBuild = infos.data.activations_left + " / " + infos.data.license_limit;
                                } else {
                                    remainingBuild = infos.data.activations_left;
                                }
                                errorMessage = '';
                                break;
                            default :
                                errorMessage = 'Your license is invalid: ' + infos.data.license;
                                break;
                        }
                        resolv({
                            "remainingBuild":remainingBuild,
                            "errorMessage":errorMessage
                        });
                    } else {
                        resolv({
                            "remainingBuild":"",
                            "errorMessage":"Cannot valid license key"
                        });
                    }
                }, function(reason){ //fail
                    reject(reason);
                });
            });
        },

        getSiberiancCMSLicenseInfo : function(licenseKey) {
            return $q(function(resolv, reject){
                $http({
                    method: 'POST',
                    url: "/system/backoffice_config_general/checksiberiancmslicense",
                    cache: false,
                    responseType:'json',
                    data: {
                        host:document.domain,
                        licenseKey:licenseKey
                    }
                })
                .then(function(infos) { //succcess
                    switch(true) {
                        //malformated
                        case !infos :
                        case !infos.data :
                        case !infos.data.message :
                            reject("Cannot valid license");
                            break;
                        default:
                            resolv(infos.data.message);
                            break;
                    }
                }, function(reason){
                    switch(true) {
                        //malformated
                        case !reason :
                        case !reason.data :
                        case !reason.data.message :
                            reject("Cannot valid license");
                            break;
                        //catched error detected
                        case reason.data.error === 1:
                            reject(reason.data.message);
                            break;
                        //The void
                        default:
                            reject("Cannot valid license");
                            break;
                    }
                });
            });
        }
    }
});