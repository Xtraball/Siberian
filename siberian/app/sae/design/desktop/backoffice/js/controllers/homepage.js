App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/backoffice", {
        controller: 'HomepageController',
        cache: false,
        templateUrl: BASE_URL+"/backoffice/index/template"
    }).when(BASE_URL+"/backoffice/index/view", {
        controller: 'HomepageController',
        cache: false,
        templateUrl: BASE_URL+"/backoffice/index/template"
    }).otherwise({
        controller: 'HomepageController',
        cache: false,
        templateUrl: BASE_URL+"/backoffice/index/template"
    });

}).controller("HomepageController", function($scope, $timeout, Header, Backoffice) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = false;
    $scope.show_notif = false;
    $scope.show_more_size = false;
    $scope.show_more_push = false;
    $scope.show_cron_modal = false;
    $scope.cron_error_show = false;
    $scope.sslLoading = true;
    $scope.sslWarning = {};
    $scope.isLoading = true;
    $scope.graphIsLoading = true;
    $scope.servicesIsLoading = true;
    $scope.messagesIsLoading = true;

    $scope.clear = function(type, message) {
        if(typeof message != "undefined") {
            if(!window.confirm(message)) {
                return false;
            }
        }

        $scope.content_loader_is_visible = true;
        Backoffice.clearCache(type).success(function (data) {
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            $scope.server_usage = data.server_usage;
            $scope.services = data.services;
            $scope.content_loader_is_visible = false;
        });
    };

    $scope.showCronModal = function () {
        $scope.show_cron_modal = true;
    };

    $scope.sslWarning = function () {
        $scope.sslLoading = true;
        Backoffice
            .sslWarning()
            .success(function (data) {
                $scope.sslWarning = data.sslData;
            })
            .error(function (data) {
                $scope.sslWarning = false;
            })
            .finally(function () {
                $scope.sslLoading = false;
            });
    };

    $scope.loadGraph = function () {
        $scope.graphIsLoading = true;

        Backoffice
        .find()
        .success(function (data) {

            if (data.notif.unread_number > 0) {
                $scope.unread_messages = data.notif.message;

                $timeout(function () {
                    $scope.show_notif = true;
                }, 1000);

                $timeout(function () {
                    $scope.show_notif = false;
                }, 10000);
            }

            var stats = data.stats;

            $scope.cssStyle = "height:420px; width:1140px;";

            var labels = stats.map(function (stat) {
                return stat[0];
            });
            var newUser = stats.map(function (stat) {
                return stat[1];
            });
            var newSale = stats.map(function (stat) {
                return stat[2];
            });
            var payment = stats.map(function (stat) {
                return stat[3];
            });

            $scope.graphSeries = data.stats_labels;
            $scope.graphLabels = labels;
            $scope.graphData = [newUser];
            var color = [
                '204,37,41'
            ];

            $scope.graphDatasetOverride = [
                {
                    borderColor: 'rgba(' + color[0] + ',1)',
                    backgroundColor: 'rgba(' + color[0] + ',0.4)',
                    pointBorderColor: 'rgba(' + color[0] + ',0.4)',
                    pointBackgroundColor: 'rgba(' + color[0] + ',1)',
                    pointHoverBackgroundColor: 'rgba(' + color[0] + ',1)',
                    pointHoverBorderColor: 'rgba(' + color[0] + ',0.4)',
                    type: 'line',
                    fill: false,
                    lineTension: 0,
                    yAxisID: 'new'
                }
            ];

            $scope.graphOptions = {
                legend: {
                    display: true,
                },
                scales: {
                    yAxes: [
                        {
                            afterBuildTicks: function (chartElem) {
                                var ticks = [];
                                for (var i = 0; i < chartElem.ticks.length; i++) {
                                    //if integer
                                    if (chartElem.ticks[i] % 1 === 0) {
                                        ticks.push(chartElem.ticks[i]);
                                    }
                                }
                                chartElem.ticks = ticks;
                                if (chartElem.start < 0) {
                                    chartElem.start = 0;
                                }
                            },
                            max: 100,
                            id: 'new',
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true
                        }
                    ]
                }
            };

        }).finally(function () {
            $scope.graphIsLoading = false;
        });
    };

    $scope.loadExternalServices = function () {
        $scope.servicesIsLoading = true;

        Backoffice
        .loadExternalServices()
        .success(function (data) {
            $scope.external_services = data.external_services;

            $scope.servicesIsLoading = false;
        }).error(function () {
            $scope.servicesIsLoading = false;
        });
    };

    $scope.loadMessages = function () {
        $scope.messagesIsLoading = true;

        Backoffice
        .loadMessages()
        .success(function (data) {
            $scope.new_messages = data.unread_messages;

            $scope.messagesIsLoading = false;
        }).error(function () {
            $scope.messagesIsLoading = false;
        });
    };

    $scope.loadContent = function () {
        $scope.isLoading = true;

        Backoffice
        .loadData()
        .success(function (data) {
            $scope.header.title = data.title;
            $scope.header.icon = data.icon;
            $scope.services = data.services;
            $scope.libraries = data.libraries;
            $scope.server_usage = data.server_usage;
            $scope.extensions = data.extensions;
            $scope.system_diagnostic = data.system_diagnostic;

            $scope.isLoading = false;
        }).error(function () {
            $scope.isLoading = false;
        });
    };

    $scope.loadContent();
    $scope.loadMessages();
    $scope.loadGraph();

    $timeout(function () {
        $scope.loadExternalServices();
    }, 1000);

    $scope.sslWarning();

});
