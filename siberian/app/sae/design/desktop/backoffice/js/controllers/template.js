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
                        let div = entry.target;
                        if (div.style.backgroundImage.length < 1) {
                            div.style.setProperty('background-image', 'url('+div.dataset.src+')', 'important');
                            // Stop observing
                            observer.unobserve(div);
                        }
                    }
                });
            },
            {}
        );

        for (let div of document.querySelectorAll('.overview-grid.not-preload')) {
            observer.observe(div);
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
            .toggleIcon(icon.link, icon.is_active)
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
