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

}).controller("TranslationEditController", function($scope, $location, $routeParams, Header, Label, SectionButton, Translations) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form_loader_is_visible = false;
    $scope.translation = {};
    Translations.type = $scope.code;

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
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.selectFile = function() {
        //if($scope.is_edit && $routeParams.lang_id)
        //    $scope.translation.country_code = $routeParams.lang_id;
        $scope.translation.collection = $scope.translation_files_data[$scope.translation.file];
    }

    $scope.notSorted = function(obj){
        if (!obj) {
            return [];
        }
        return Object.keys(obj);
    }
    
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

});
