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

}).controller("TranslationEditController", function($http, $scope, $location, $routeParams, Header, Label, SectionButton, Translations, Url) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form_loader_is_visible = false;
    $scope.translation = {};
    $scope.can_translate = false;
    Translations.type = $scope.code;

    $scope.currentClass = new Array();

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

    $scope.available_target = new Array("be", "ca", "cs", "da", "de", "el", "es", "et", "fi", "fr", "hu", "it", "lt", "lv", "mk", "nl", "no", "pt", "ru", "sk", "sl", "sq", "sv", "tr", "uk");

    $scope.translate = function(key, target) {
        $http({
            method: 'POST',
            url: Url.get("translation/backoffice_edit/translate"),
            data: {"text": key, "target": target},
            cache: false,
            responseType:'json'
        }).then(function(response) {
            if(response.data && response.data.result && response.data.result.text) {
                $scope.translation.collection[key] = response.data.result.text[0];
            }
        }, function (response) {
            if(response.data && response.data.message) {
                $scope.message.setText(response.data.message)
                    .isError(true)
                    .show()
                ;

                return false;
            }
        });

        return true;
    };

    $scope.translateAll = function() {
        var breakOnError = false;

        angular.forEach($scope.translation.collection, function(value, key) {

            if(!breakOnError && !$scope.translate(key, $scope.translation.country_code.split("_")[0])) {
                breakOnError = true;
            }

            $scope.updateClass(key);
        });
    };

    $scope.translateMissing = function() {
        var breakOnError = false;

        angular.forEach($scope.translation.collection, function(value, key) {
            if(!breakOnError && value === null || value.trim() == "") {
                if(!$scope.translate(key, $scope.translation.country_code.split("_")[0])) {
                    breakOnError = true;
                }

                $scope.updateClass(key);
            }
        });
    };

});
