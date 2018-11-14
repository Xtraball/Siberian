App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/template/backoffice_category_list", {
        controller: 'TemplateCategoryListController',
        templateUrl: BASE_URL+"/template/backoffice_category_list/template"
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

});
