App.directive('viewLayoutAdapter', function ($rootScope, LayoutService) {

    return {
        link: function ($scope, element) {

            $scope.updateClass = function(){

                var cssClass = null;
                switch(LayoutService.properties.menu.position) {
                    case 'left':
                        cssClass = 'with-left-sidebar';
                        break;
                    case 'bottom':
                        cssClass = 'with-bottom-sidebar';
                        break;
                }

                if (cssClass !== null){
                    if(LayoutService.properties.menu.isVisible) {
                        element.addClass(cssClass);
                    } else {
                        element.removeClass(cssClass);
                    }
                }
            };

            LayoutService.getData().then(function(data){
                element.addClass('layout-' + data.layout_id);
            });

            $scope.$watch(function () {
                return LayoutService.properties.menu.isVisible;
            }, function (isVisible) {
                $scope.updateClass();
            });
        }
    };
    
});