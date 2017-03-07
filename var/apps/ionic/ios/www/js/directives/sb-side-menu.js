App.directive('sbSideMenu', function ($rootElement, $rootScope, $ionicSideMenuDelegate, $ionicHistory, HomepageLayout, ContextualMenu) {
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
                angular.element($rootElement).addClass(("layout-"+scope.layout_id).replace(/[^a-zA-Z0-9_\-]+/, "-").replace(".", "-").replace(/\-\-*/, "-"));
            });

            /** Custom go back, works with/without side-menus */
            scope.goBack = function() {
                $ionicHistory.goBack();
            };

            /** Special trick to handle manual updates. */
            scope.checkForUpdate = function() {
                $rootScope.checkForUpdate();
            };

            scope.showLeft = function() {
                return (scope.layout_id && (scope.layout.menu.position === 'left'));
            };

            scope.showRight = function() {
                return (scope.layout_id && (scope.layout.menu.position === 'right'));
            };

            scope.showBottom = function() {
                return (scope.layout_id && (scope.layout.menu.position === 'bottom') && (scope.layout.menu.visibility === 'homepage'));
            };

            scope.showAlways = function() {
                return (scope.layout_id && (scope.layout.menu.position === 'bottom') && (scope.layout.menu.visibility === 'always'));
            };

            scope.contextualMenuSideWidth = function() {
                return ContextualMenu.width;
            };

            scope.contextualMenuIsEnabled = function() {
                return ContextualMenu.isEnabled;
            };

            scope.contextualMenuExists = function() {
                return ContextualMenu.exists;
            };

            scope.contextualMenu = function() {
                return ContextualMenu.templateURL;
            };
        }
    };
});
