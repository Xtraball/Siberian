App.directive('sbSideMenu', function ($rootScope, $ionicSideMenuDelegate, $ionicHistory, HomepageLayout) {
    return {
        restrict: 'E',
        replace: true,
        scope: {},
        templateUrl: "templates/page/side-menu.html",
        link: function(scope, element) {

            /** Defining the global functionnalities of the page */
            HomepageLayout.getFeatures().then( function (features) {
                scope.layout = HomepageLayout.properties;
                scope.layout_id = HomepageLayout.properties.layoutId;
            });

            /** Custom go back, works with/without side-menus */
            scope.goBack = function() {
                $ionicHistory.goBack();
            };

            scope.showLeft = function() {
                return (scope.layout_id && scope.layout.menu.position == 'left');
            };

            scope.showRight = function() {
                return (scope.layout_id && scope.layout.menu.position == 'right');
            };

            scope.showBottom = function() {
                return (scope.layout_id && scope.layout.menu.position == 'bottom' && scope.layout.menu.visibility == 'homepage');
            };

            scope.showAlways = function() {
                return (scope.layout_id && scope.layout.menu.position == 'bottom' && scope.layout.menu.visibility == 'always');
            };
        }
    };
});