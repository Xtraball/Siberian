App.service('Application', function($http, $interval, $ionicPopup, $rootScope, $timeout, $translate, $window, Dialog, Url) {

    var service = {};

    service.app_id = null;
    service.app_name = null;

    service.is_customizing_colors = $window.location.href.indexOf("application/mobile_customization_colors/") >= 0;

    service.showCacheDownloadModal = function() {
        $rootScope.progressBarPercent = 0;
        $rootScope.showProgressBar = false;

        var title = $translate.instant("Offline content");
        var message = $translate.instant("Do you want to download all the contents now to access it when offline? If you do, we recommend you to use a WiFi connection.");
        var buttons = [$translate.instant("No"), $translate.instant("Yes")];

        Dialog.confirm(title, message, buttons, "text-center").then(function(res) {

            if(((typeof res  == "number") && res == 2) || ((typeof res  == "boolean") && res)) {

                var progress_type = "CIRCLE";
                if(ionic.Platform.isAndroid()) {
                    progress_type = "BAR";
                }
                window.plugins.ProgressView.show($translate.instant("Downloading..."), progress_type, false, "DEVICE_DARK");

                $rootScope.showProgressBar = true;

                $http.get(Url.get("application/mobile_data/findall")).success(function (data) {

                    var data = Object.keys(data).map(function (key) {
                        return data[key]
                    });
                    var image_codes = ['"image_url":', '"validated_image_url":', '"icon":', '"picture":', '"image":', '"picto":', '"picto_url":', '"flag_icon":', '"url":'];

                    var progress = 0;
                    var total = data.length;

                    angular.forEach(data, function (value) {
                        //if(value.indexOf("sb-http") != -1) {
                        //var e = $window.document.createElement("iframe");
                        //e.src = value.replace("sb-http", "http");
                        //
                        //var timer = $interval(function() {
                        //    if (e.contentWindow.document.readyState == 'complete') {
                        //        $interval.cancel(timer);
                        //        $window.document.body.removeChild(e);
                        //        progress++;
                        //        $rootsScope.progressBarPercent = parseInt(progress * 100 / total);
                        //
                        //        if ($rootScope.progressBarPercent >= 100) $timeout(function () {
                        //            $rootScope.showProgressBar = false;
                        //        }, 500);
                        //    }
                        //}, 1000);
                        //
                        //$window.document.body.appendChild(e);
                        //} else {

                        //caching each data uris
                        $http({
                            method: 'GET',
                            url: value,
                            cache: !$rootScope.isOverview,
                        }).success(function (result) {

                            if (typeof result == "object") {

                                var data = JSON.stringify(result);

                                angular.forEach(image_codes, function (image_code) {
                                    var begin_index = 0;

                                    while (begin_index != -1) {
                                        begin_index = data.indexOf(image_code, begin_index + 1);

                                        if (begin_index != -1) {
                                            var image_url = data.substr(begin_index);
                                            image_url = image_url.substr(0, image_url.indexOf(',')).replace(image_code, '').replace(/["}{\]]/g, '');

                                            if (angular.isDefined(image_url) && image_url != "null" && image_url != "" && (/png|jpg|jpeg|gif/i).test(image_url)) {

                                                if(image_url.indexOf("http") < 0) {
                                                    image_url = DOMAIN + image_url;
                                                }

                                                $http({
                                                    method: 'GET',
                                                    url: image_url,
                                                    cache: !$rootScope.isOverview
                                                });
                                            }
                                        }
                                    }
                                });
                            }

                        }).finally(function () {
                            progress++;
                            $rootScope.progressBarPercent = (progress / total).toFixed(2);
                            window.plugins.ProgressView.setProgress($rootScope.progressBarPercent);

                            if ($rootScope.progressBarPercent >= 1) {
                                $timeout(function () {
                                    $rootScope.showProgressBar = false;
                                    window.plugins.ProgressView.hide();
                                }, 1000);
                            }
                        });
                        //}
                    });
                });

            }
        });

    };

    service.generateWebappConfig = function() {
        return $http({
            method: 'GET',
            url: Url.get("application/mobile/generatewebappconfig"),
            cache: false,
            responseType:'json'
        });
    };

    return service;
});