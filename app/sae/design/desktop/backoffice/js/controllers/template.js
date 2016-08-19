App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/template/backoffice_category_list", {
        controller: 'TemplateCategoryListController',
        templateUrl: BASE_URL+"/template/backoffice_category_list/template"
    });

}).controller("TemplateCategoryListController", function($scope, Header, TemplateCategory) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

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

        $scope.form_loader_is_visible = true;

        TemplateCategory.save($scope.getCategories()).success(function(data) {
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
            $scope.form_loader_is_visible = false;
        });
    }

    $scope.getCategories = function() {
        var categories = new Array();
        angular.forEach($scope.template.columns, function(columns) {
            angular.forEach(columns, function(category) {
                categories.push(category);
            });
        });
        return categories;
    };

});
