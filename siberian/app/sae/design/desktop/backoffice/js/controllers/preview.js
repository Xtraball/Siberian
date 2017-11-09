App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/preview/backoffice_list", {
        controller: 'PreviewListController',
        templateUrl: BASE_URL+"/preview/backoffice_list/template"
    }).when(BASE_URL+"/preview/backoffice_edit/", {
        controller: 'PreviewEditController',
        templateUrl: BASE_URL+"/preview/backoffice_edit/template"
    }).when(BASE_URL+"/preview/backoffice_edit/preview_id/:preview_id", {
        controller: 'PreviewEditController',
        templateUrl: BASE_URL+"/preview/backoffice_edit/template"
    });

}).controller("PreviewListController", function($scope, $location, Header, SectionButton, Label, Preview) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    $scope.button = new SectionButton(function() {
        $location.path("preview/backoffice_edit");
    });

    Preview.loadListData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Preview.findAll().success(function(data) {
        $scope.previews = data;
        $scope.content_loader_is_visible = false;
    });

    $scope.deletePreview = function(preview_id) {
        if(confirm("This will delete all images and translations of this preview. Sure?")) {
            Preview.fullDelete(preview_id).success(function(data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                var previews_tmp = new Array();
                angular.forEach($scope.previews, function(preview){
                   if(preview.id != preview_id){
                        previews_tmp.push(preview);
                   }
                });
                $scope.previews = previews_tmp;

            }).error(function(data){
                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;
            });
        }
    }

}).controller("PreviewEditController", function($scope, $location, $routeParams, $filter, Header, SectionButton, Label, Preview, FileUploader, Url) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    $scope.opts = {containment: '.sortable-container'};
    $scope.previews = {};
    $scope.options = null;
    $scope.option = {};

    Preview.loadListData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Preview.find($routeParams.preview_id).success(function(data) {
        $scope.previews = data.previews ? data.previews : {};
        $scope.options = data.options ? data.options : $scope.options;
        $scope.languages = data.languages;
        $scope.language_code = data.current_language;
        $scope.prepareUploader();
        $scope.findPreviewByLanguage($scope.language_code);
        $scope.section_title_one = data.section_title_one;
        $scope.section_title_two = data.section_title_two;
        $scope.applications_section_title = data.applications_section_title;
        $scope.country_codes = data.country_codes;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.image_uploader = new FileUploader({
        url: Url.get("preview/backoffice_edit/upload")
    });

    $scope.prepareUploader = function() {

        $scope.image_uploader.filters.push({
            name: 'image_only',
            fn: function (item, options) {
                var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                return '|jpg|png|jpeg|gif|'.indexOf(type) !== -1;
            }
        });

        $scope.image_uploader.onWhenAddingFileFailed = function(item, filter, options) {
            $scope.message.setText(Label.uploader.error.type.image).isError(true).show();
        };

        $scope.image_uploader.onAfterAddingFile = function(item, filter, options) {
            item.upload();
        };

        $scope.image_uploader.onSuccessItem = function(fileItem, response, status, headers) {
            if(angular.isObject(response) && response.success) {

                $scope.preview.images.push({
                    "id": $scope.preview.images.length + 1,
                    "link":Url.get("var/tmp/" + response.filename),
                    "filename":response.filename,
                    "new":1,
                    "to_delete":0
                });

                $scope.message.setText(response.message)
                    .isError(false)
                    .show()
                ;

            } else {
                $scope.message.setText(Label.uploader.error.general)
                    .isError(true)
                    .show()
                ;
            }
        };

        $scope.image_uploader.onErrorItem = function(fileItem, response, status, headers) {
            $scope.message.setText(response.message)
                .isError(true)
                .show()
            ;
        };
    };

    $scope.findPreviewByLanguage = function(language_code) {

        if(angular.isDefined($scope.previews[language_code])) {
            $scope.preview = $scope.previews[language_code];
        } else {
            var new_preview = {
                "language_code": language_code,
                "title": "",
                "description": "",
                "images": new Array()
            };
            $scope.preview = new_preview;
            $scope.previews[language_code] = new_preview;
        }
        $scope.language_code = language_code;

    };

    $scope.save = function() {
        Preview.save($routeParams.preview_id,$scope.option.id,$scope.previews).success(function(data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            $location.path("/preview/backoffice_list");
        }).error(function(data){
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
        });
    };

    $scope.deleteImage = function(image_id) {
        angular.forEach($scope.preview.images, function(image) {
            if(image.id == image_id) {
                image.to_delete = 1;
            }
        })
    };

    $scope.deleteTranslation = function() {
        if(confirm("This will delete all images and translation of this language. Sure?")) {
            Preview.delete($routeParams.preview_id,$scope.language_code).success(function(data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                var new_preview = {
                    "language_code": $scope.language_code,
                    "title": "",
                    "description": "",
                    "images": new Array()
                };
                $scope.preview = new_preview;
                $scope.previews[$scope.language_code] = new_preview;
            }).error(function(data){
                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;
            });
        }
    };

});