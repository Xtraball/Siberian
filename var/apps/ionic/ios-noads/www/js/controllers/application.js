/*global
 App, IMAGE_URL, BASE_PATH
 */
App.config(function($stateProvider) {

    $stateProvider.state('colors-view', {
        url: BASE_PATH+"/application/mobile_customization_colors/",
        controller: 'ApplicationColorsController',
        templateUrl: "templates/application/l1/colors/view.html"
    }).state('tc-view', {
        url: BASE_PATH+"/application/mobile_tc_view/index/tc_id/:tc_id",
        controller: 'ApplicationTcController',
        templateUrl: "templates/application/l1/tc/view.html"
    });

}).controller('ApplicationColorsController', function($scope, $timeout, $window, HomepageLayout) {

    HomepageLayout.getFeatures().then(function () {
        $scope.tabbar_is_transparent = HomepageLayout.properties.tabbar_is_transparent;
    });

    $scope.images = {
        mark_hamill:            IMAGE_URL+"/images/customization/card/mark-hamill.jpg",
        harrison_ford:          IMAGE_URL+"/images/customization/card/harrison-ford.jpg",
        carrie_fisher:          IMAGE_URL+"/images/customization/card/carrie-fisher.jpg",
        skywalker_spaceship:    IMAGE_URL+"/images/customization/card/skywalker-spaceship.jpg"
    };

    $scope.buttons = [
        {label: "Phone",    icon: "ion-ios-telephone-outline"},
        {label: "Locate",   icon: "ion-ios-location-outline"},
        {label: "Email",    icon: "ion-ios-email-outline"},
        {label: "Website",  icon: "ion-ios-world-outline"},
        {label: "Facebook", icon: "ion-social-facebook-outline"},
        {label: "Twitter",  icon: "ion-social-facebook-outline"}
    ];

    $scope.checkboxes = [{is_checked: false}, {is_checked: false}, {is_checked: true}, {is_checked: true}, {is_checked: true}, {is_checked: false}, {is_checked: false}, {is_checked: true}];
    $scope.radios = [0, 1, 2, 3, 4, 5, 6, 7];
    $scope.radio_value = 1;
    $scope.toggles = [{is_selected: false}, {is_selected: true}, {is_selected: true}, {is_selected: true}, {is_selected: false}, {is_selected: false}, {is_selected: true}];

    $scope.tooltip = {
        is_visible: true,
        collection: [{id: 1, "name": "Lorem Ipsum"}, {id: 2, "name": "Lorem Ipsum Loreo"}, {id: 3, "name": "Lorem Ipsum Loreo"}, {id: 4, "name": "Lorem Ipsum Loreo"}, {id: 5, "name": "Lorem Ipsum Loreo"}, {id: 6, "name": "Lorem Ipsum Loreo"}],
        current_item: {id: 2},
        selectTooltipItem: function(item) {
            $scope.tooltip.current_item = item;
        }
    };

    $window.displayElement = function(element, title) {

        $timeout(function() {
            $scope.displayed_element = element;
            if($scope.displayed_element === "homepage") {
                $scope.homepage_is_initialized = true;
            }
            $scope.page_title = title;
        });

    };

}).controller('ApplicationTcController', function($scope, $stateParams, $sce, Tc) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(!!args.isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.loadContent = function () {

        Tc.find($stateParams.tc_id).success(function (data) {

            $scope.html_file_path = $sce.trustAsResourceUrl(data.html_file_path);
            $scope.page_title = data.page_title;

        }).finally(function () {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});