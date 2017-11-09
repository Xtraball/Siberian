App.config(function ($routeProvider) {
    $routeProvider
        .when(BASE_URL + '/push/backoffice_certificate', {
            controller: 'PushController',
            templateUrl: BASE_URL + '/push/backoffice_certificate/template'

        }).when(BASE_URL + '/push/backoffice_global', {
            controller: 'PushGlobalController',
            templateUrl: BASE_URL + '/push/backoffice_global/template'
        });
}).controller('PushController', function ($scope, Header, Push) {
    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    Push.loadData().success(function (data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Push.findAll().success(function (push) {
        $scope.push = push;
    }).finally(function () {
        $scope.content_loader_is_visible = false;
    });

    $scope.saveKeys = function () {
        $scope.form_loader_is_visible = true;

        Push.save($scope.push.keys).success(function (data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
        }).error(function (data) {
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
        }).finally(function () {
            $scope.form_loader_is_visible = false;
        });
    };
}).controller('PushGlobalController', function ($scope, Header, Push) {
    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;


    $scope.applications = [];
    $scope.checked = [];

    $scope.push_message = {
        title: null,
        message: null,
        send_to_all: false,
        devices: 'all',
        open_url: false,
        url: null
    };

    $scope.perPage = 10;
    $scope.page = 0;
    $scope.clientLimit = 250;

    $scope.urlParams = {
        filter: '',
        order: 'app_id',
        by: true,
        globalPush: true,
        published_only: true
    };

    Push.globalFindAll()
        .success(function (data) {
            $scope.header.title = data.title;
            $scope.header.icon = data.icon;
        }).finally(function () {
            $scope.content_loader_is_visible = false;
        });

    $scope.globalSend = function () {
        $scope.content_loader_is_visible = true;

        var params = {
            title: $scope.push_message.title,
            message: $scope.push_message.message,
            send_to_all: $scope.push_message.send_to_all,
            devices: $scope.push_message.devices,
            checked: $scope.checked,
            open_url: $scope.push_message.open_url,
            url: $scope.push_message.url
        };

        Push.globalSend(params).success(function (data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;

            $scope.push_message = {
                title: null,
                message: null,
                send_to_all: false,
                devices: 'all',
                open_url: false,
                url: null
            };

            $scope.checked = [];
        }).error(function (data) {
            $scope.message.setText(data.message)
                .isError(true)
                .show()
            ;
        }).finally(function () {
            $scope.content_loader_is_visible = false;
        });
    };
});
