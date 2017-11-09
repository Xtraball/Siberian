App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/sales/backoffice_order_list", {
        controller: 'OrderListController',
        templateUrl: BASE_URL+"/sales/backoffice_order_list/template"
    }).when(BASE_URL+"/sales/backoffice_order_list/index", {
        controller: 'OrderListController',
        templateUrl: BASE_URL+"/sales/backoffice_order_list/template"
    }).when(BASE_URL+"/sales/backoffice_order_view", {
        controller: 'OrderViewController',
        templateUrl: BASE_URL+"/sales/backoffice_order_view/template"
    }).when(BASE_URL+"/sales/backoffice_inorderiew/index", {
        controller: 'OrderViewController',
        templateUrl: BASE_URL+"/sales/backoffice_order_view/template"
    });

}).controller("OrderListController", function($scope, Header, Order) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    Order.loadListData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Order.findAll().success(function(data) {

    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

}).controller("OrderViewController", function($scope, $location, Header, Order, Url) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.button.left.action = function() {
        $location.path(Url.get("sales/backoffice_order_list"));
    };
    $scope.content_loader_is_visible = true;

    Order.loadViewData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Order.find().finally(function() {
        $scope.content_loader_is_visible = false;
    });

});
