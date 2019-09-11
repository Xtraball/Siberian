/**
 * Cabride backoffice settings
 */
App.config(function ($routeProvider) {
    $routeProvider
		.when(BASE_URL + '/cabride/backoffice_view', {
			controller: 'CabrideViewController',
			templateUrl: BASE_URL + '/cabride/backoffice_view/template'
		});
}).controller('CabrideViewController', function ($scope, $window, Header, Cabride, $interval) {
    angular.extend($scope, {
        header: new Header(),
        content_loader_is_visible: true,
		settings: {},
		logContent: "",
        logError: false,
        logErrorMessage: ""
    });

    $scope.header.loader_is_visible = false;

    Cabride
        .loadData()
		.success(function (payload) {
			$scope.header.title = payload.title;
			$scope.header.icon = payload.icon;
			$scope.settings = payload.settings;
		}).finally(function () {
			$scope.content_loader_is_visible = false;
		});

    $scope.save = function () {
        $scope.content_loader_is_visible = true;
        Cabride
			.save($scope.settings)
            .success(function (payload) {
                $scope.message
                .setText(payload.message)
                .isError(false)
                .show();
            }).error(function (payload){
                $scope.message
                .setText(payload.message)
                .isError(true)
                .show();
            }).finally(function () {
                $scope.content_loader_is_visible = false;
			});
	};

    $scope.restartSocket = function () {
        $scope.content_loader_is_visible = true;
        Cabride
        .restartSocket()
        .success(function (payload) {
            $scope.message
            .setText(payload.message)
            .isError(false)
            .show();

            $scope.offset = 0;
        }).error(function (payload){
            $scope.message
            .setText(payload.message)
            .isError(true)
            .show();
        }).finally(function () {
            $scope.content_loader_is_visible = false;
        });
    };

    $interval(function () {
        Cabride
        .liveLog($scope.offset)
        .success(function (payload) {
            $scope.logError = false;
            $scope.logErrorMessage = "";
            $scope.offset = payload.offset;
            $scope.logContent += payload.txtContent;
        }).error(function (payload){
            $scope.offset = 0;
            $scope.logError = true;
            $scope.logErrorMessage = payload.message;
        })
    }, 3000);
});
