App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/backoffice", {
        controller: 'HomepageController',
        templateUrl: BASE_URL+"/backoffice/index/template"
    }).when(BASE_URL+"/backoffice/index/view", {
        controller: 'HomepageController',
        templateUrl: BASE_URL+"/backoffice/index/template"
    }).otherwise({
        controller: 'HomepageController',
        templateUrl: BASE_URL+"/backoffice/index/template"
    });

}).controller("HomepageController", function($scope, $timeout, Header, Backoffice) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.show_notif = false;

    Backoffice.loadData().success(function (data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Backoffice.find().success(function(data) {

        if(data.notif.unread_number > 0) {
            $scope.unread_messages = data.notif.message;

            $timeout(function() {
                $scope.show_notif = true;
            }, 1000);

            $timeout(function() {
                $scope.show_notif = false;
            }, 10000);
        }

        var stats = data.stats;

        $scope.cssStyle = "height:400px; width:1140px;";

        $scope.chartObject = {
            "type": "AreaChart",
            "displayed": true,
            "data": {
                "cols": [
                    {"id": "date","label": "Date","type": "string","p": {}},
                    {"id": "user-id","label": "New Users","type": "number","p": {}}
                ],
                "rows": []
            },
            "options": {
                "title": "New users this month",
                "isStacked": "false",
                "fill": 20,
                "displayExactValues": true,
                "vAxis": {
                    //"title": "Unit",
                    "gridlines": {
                        "count": 10
                    }
                },
                "hAxis": {
                    //"title": "Date"
                }
            },
            "formatters": {},
            "view": {}
        };


        var dataLength = stats.length;
        for (var i = 0; i < dataLength; i++) {

            var row = {
                "c": [
                    {"v": stats[i][0],"p": {}},
                    {"v": stats[i][1], "p": {}}
                ]
            };
            $scope.chartObject.data.rows.push(row);

        }



    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

});
