App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/template/backoffice_category_list", {
        controller: 'TemplateCategoryListController',
        templateUrl: BASE_URL+"/template/backoffice_category_list/template"
    });

    $routeProvider.when(BASE_URL+"/template/backoffice_icons_list", {
        controller: 'TemplateIconsListController',
        templateUrl: BASE_URL+"/template/backoffice_icons_list/template"
    });

}).controller("TemplateCategoryListController", function($scope, Header, TemplateCategory) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    $scope.viewType = 'grid';

    TemplateCategory.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    TemplateCategory.findAll().success(function(data) {
        $scope.template = data;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.save = function() {

        $scope.content_loader_is_visible = true;

        TemplateCategory
            .save($scope.template.categories)
            .success(function(data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
            }).error(function(data) {

                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;

            }).finally(function() {
                $scope.content_loader_is_visible = false;
            });
    };

    $scope.toggleTemplate = function (template) {
        $scope.content_loader_is_visible = true;
        TemplateCategory
            .toggleTemplate(template.template_id, template.is_active)
            .success(function (data) {
                var message = '';
                if (angular.isObject(data) && angular.isDefined(data.message)) {
                    message = data.message;
                    $scope.message.isError(false);
                }

                $scope.message.setText(message)
                .show()
                ;
            }).error(function (data) {
                var message = '';
                if (angular.isObject(data) && angular.isDefined(data.message)) {
                    message = data.message;
                }

                $scope.message.setText(message)
                .isError(true)
                .show()
                ;
            }).finally(function () {
                $scope.content_loader_is_visible = false;
            });
    };

}).controller("TemplateIconsListController", function($scope, $timeout, Header, TemplateIcons) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    $scope.settings = {
        icons_library_default_filter: 'none'
    };

    $scope.startObserver = function () {
        // Observer to lazy load images as they display
        var observer = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.intersectionRatio > 0.0) {
                        img = entry.target;
                        if (!img.hasAttribute('src')) {
                            img.setAttribute('src', img.dataset.src);
                            // Stop observing
                            observer.unobserve(img);
                        }
                    }
                });
            },
            {}
        );

        for (let img of document.querySelectorAll('.view-grid img.not-preload')) {
            observer.observe(img);
        }
    };

    TemplateIcons
        .loadData()
        .success(function(payload) {
            $scope.header.title = payload.title;
            $scope.header.icon = payload.icon;
            $scope.settings = payload.settings;
        });

    TemplateIcons
        .findAll()
        .success(function(payload) {

            $timeout(function () {
                $scope.icons = payload.icons;
                $scope.strings = payload.strings;
            });

        }).finally(function() {
            $scope.content_loader_is_visible = false;
        });

    $scope.save = function () {
        $scope.content_loader_is_visible = true;

        TemplateIcons
            .saveSettings($scope.settings)
            .success(function(data) {
                $scope
                    .message
                    .setText(data.message)
                    .isError(false)
                    .show();
            }).error(function(data) {
                $scope
                    .message
                    .setText(data.message)
                    .isError(true)
                    .show();
            }).finally(function() {
                $scope.content_loader_is_visible = false;
            });
    };

    $scope.toggleIcon = function (icon) {
        $scope.content_loader_is_visible = true;
        TemplateIcons
            .toggleIcon(icon.image_id, icon.is_active)
            .success(function (data) {
                var message = '';
                if (angular.isObject(data) && angular.isDefined(data.message)) {
                    message = data.message;
                    $scope.message.isError(false);
                }

                $scope.message.setText(message)
                    .show()
                ;
            }).error(function (data) {
                var message = '';
                if (angular.isObject(data) && angular.isDefined(data.message)) {
                    message = data.message;
                }

                $scope.message.setText(message)
                    .isError(true)
                    .show()
                ;
            }).finally(function () {
                $scope.content_loader_is_visible = false;
            });
    };
});
