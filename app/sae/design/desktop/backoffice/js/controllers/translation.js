App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/translation/backoffice_list", {
        controller: 'TranslationController',
        templateUrl: BASE_URL+"/translation/backoffice_list/template",
        code: "list"
    }).when(BASE_URL+"/translation/backoffice_edit", {
        controller: 'TranslationEditController',
        templateUrl: BASE_URL+"/translation/backoffice_edit/template",
        code: "edit"
    }).when(BASE_URL+"/translation/backoffice_edit/lang_id/:lang_id", {
        controller: 'TranslationEditController',
        templateUrl: BASE_URL+"/translation/backoffice_edit/template",
        code: "edit"
    });

}).controller("TranslationController", function($scope, $location, Header, Label, SectionButton, Translations) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form_loader_is_visible = false;
    Translations.type = $scope.code;

    $scope.button = new SectionButton(function() {
        $location.path("translation/backoffice_edit");
    });

    Translations.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Translations.findAll().success(function(data) {
        $scope.languages = data;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

}).controller("TranslationEditController", function($http, $scope, $location, $routeParams, $queue, Header, Label,
                                                    SectionButton, Translations, Url) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form_loader_is_visible = false;
    $scope.translation = {};
    $scope.can_translate = false;
    Translations.type = $scope.code;

    $scope.yandexTranslation = {
        showProgress: false,
        progress: 0
    };

    $scope.currentClass = [];

    $scope.updateClass = function(key) {
        $scope.currentClass[key] = "highlight";
    };

    Translations.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Translations.find($routeParams.lang_id).success(function(data) {
        $scope.translation.country_code = data.country_code;
        $scope.languages = data;
        $scope.section_title = data.section_title;
        $scope.countries = data.country_codes;
        $scope.translation_files = data.translation_files;
        $scope.translation_files_data = data.translation_files_data;
        $scope.info = data.info;
        $scope.is_edit = data.is_edit;
        if($scope.translation.country_code) {
            $scope.can_translate = ($scope.available_target.indexOf($scope.translation.country_code.split("_")[0]) != -1);
        } else {
            $scope.can_translate = false;
        }
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.changeCountry = function() {
        $scope.can_translate = ($scope.available_target.indexOf($scope.translation.country_code.split("_")[0]) != -1);
    };

    $scope.selectFile = function() {
        $scope.translation.collection = $scope.translation_files_data[$scope.translation.file];
    };

    $scope.notSorted = function(obj){
        if (!obj) {
            return [];
        }
        return Object.keys(obj);
    };
    
    $scope.save = function() {

        $scope.form_loader_is_visible = true;

        Translations.save($scope.translation).success(function(data) {

            $scope.is_edit = true;

            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(false);
            } else {
                $scope.message.isError(true);
            }
            $scope.message.setText(message)
                .show()
            ;

        }).error(function(data) {
            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }
    
            $scope.message.setText(message)
                .isError(true)
                .show()
            ;
        }).finally(function() {
            $scope.form_loader_is_visible = false;
        });
    };

    $scope.available_target = ["be", "ca", "cs", "da", "de", "el", "es", "et", "fi", "fr", "hu", "it", "lt", "lv", "mk", "nl", "no", "pt", "ru", "sk", "sl", "sq", "sv", "tr", "uk"];

    $scope.translate = function(key, target) {
        return $http({
            method: 'POST',
            url: Url.get("translation/backoffice_edit/translate"),
            data: {"text": key, "target": target},
            cache: false,
            responseType:'json'
        });
    };

    $scope.translateAll = function() {

        var keys = [];
        var size = 0;
        var remain = 0;
        var callbackTranslate = function (key) {
            var value = keys[key];
            if(!breakOnError) {
                $scope.translate(key, $scope.translation.country_code.split("_")[0])
                    .then(function(response) {
                        if(response.data && response.data.result && response.data.result.text) {
                            $scope.translation.collection[key] = response.data.result.text[0];
                        }
                        $scope.updateClass(key);
                        $scope.yandexTranslation.progress = Math.round((size - remain) / size * 100);
                        remain = remain - 1;
                    }, function (response) {
                        if(response.data && response.data.message) {
                            $scope.message.setText(response.data.message)
                                .isError(true)
                                .show()
                            ;
                        }
                        breakOnError = true;
                    });
            } else {
                $scope.yandexTranslation.progress = 0;
                $scope.yandexTranslation.showProgress = false;
            }

        };

        var breakOnError = false,
            currentLanguage = $scope.translation.country_code.split("_")[0],
            translateQueue = $queue.queue(callbackTranslate, {
                delay: 100,
                paused: true,
                complete: function() {
                    $scope.yandexTranslation.showProgress = false;
                }
            });

        angular.forEach($scope.translation.collection, function(value, key) {
            keys[key] = value;
            translateQueue.add(key);
        });

        size = translateQueue.size();
        remain = size;
        translateQueue.start();
        $scope.yandexTranslation.showProgress = true;

    };

    $scope.translateMissing = function() {

        var keys = [];
        var size = 0;
        var remain = 0;
        var callbackTranslate = function (key) {
            if(!breakOnError) {
                $scope.translate(key, $scope.translation.country_code.split("_")[0])
                    .then(function(response) {
                        if(response.data && response.data.result && response.data.result.text) {
                            $scope.translation.collection[key] = response.data.result.text[0];
                        }
                        $scope.updateClass(key);
                        $scope.yandexTranslation.progress = Math.round((size - remain) / size * 100);
                        remain = remain - 1;
                    }, function (response) {
                        if(response.data && response.data.message) {
                            $scope.message.setText(response.data.message)
                                .isError(true)
                                .show()
                            ;
                        }
                        breakOnError = true;
                    });
            } else {
                $scope.yandexTranslation.progress = 0;
                $scope.yandexTranslation.showProgress = false;
            }
        };

        var breakOnError = false,
            currentLanguage = $scope.translation.country_code.split("_")[0],
            translateQueue = $queue.queue(callbackTranslate, {
                delay: 100,
                paused: true,
                complete: function() {
                    $scope.yandexTranslation.showProgress = false;
                }
            });

        angular.forEach($scope.translation.collection, function(value, key) {
            if(value === null || value === "") {
                keys[key] = value;
                translateQueue.add(key);
            }
        });

        size = translateQueue.size();
        remain = size;
        translateQueue.start();
        $scope.yandexTranslation.showProgress = true;

    };

});
