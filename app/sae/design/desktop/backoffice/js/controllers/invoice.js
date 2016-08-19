App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/sales/backoffice_invoice_list", {
        controller: 'InvoiceListController',
        templateUrl: BASE_URL+"/sales/backoffice_invoice_list/template"
    }).when(BASE_URL+"/sales/backoffice_invoice_view/invoice_id/:invoice_id", {
        controller: 'InvoiceViewController',
        templateUrl: BASE_URL+"/sales/backoffice_invoice_view/template"
    });

}).controller("InvoiceListController", function($scope, Header, Invoice) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    Invoice.loadListData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Invoice.findAll().success(function(invoices) {
        $scope.invoices = invoices;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

}).controller("InvoiceViewController", function($scope, $location, $routeParams, Header, Invoice, Url) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.button.left.action = function() {
        $location.path(Url.get("sales/backoffice_invoice_list"));
    };
    $scope.content_loader_is_visible = true;

    Invoice.loadViewData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Invoice.find($routeParams.invoice_id).success(function(data) {
        $scope.invoice = data.invoice;
        $scope.section_title = data.section_title;
        $scope.my_info = data.my_info;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

});
