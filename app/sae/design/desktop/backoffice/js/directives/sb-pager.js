App.directive('sbPager', function($timeout) {
    return {
        restrict: 'A',
        replace: false,
        transclude:true,
        scope: {
            items: '&',
            pageItems: '&'
        },
        template:'<ng-transclude></ng-transclude><span class="pull-right pagination">'
        + '<ul>'
        + '<li><a href="#" class="bt" ng-class="{disabled:isFirstPage()}" ng-disabled="isFirstPage()" ng-click="firstPage()"><i class="fa fa-backward"></i></a></li>'
        + '<li><a href="#" class="bt" ng-class="{disabled:isFirstPage()}" ng-disabled="isFirstPage()" ng-click="previousPage()"><i class="fa fa-chevron-left"></i></a></li>'
        +   '<li ng-repeat="page in getPageList()">'
        +       '<a href="#" ng-click="goToPage(page-1)" ng-class="{\'text-danger\':currentPage == (page-1)}">{{page}}</a>'
        +   '</li>'
        + '<li><a href="#" class="bt" ng-class="{disabled:isLastPage()}" ng-disabled="isLastPage()" ng-click="nextPage()"><i class="fa fa-chevron-right"></i></a></li>'
        + '<li><a href="#" class="bt" ng-class="{disabled:isLastPage()}" ng-disabled="isLastPage()" ng-click="lastPage()"><i class="fa fa-forward"></i></a></li>'
        + '</ul>'
        + '<div class="display"><span class="pull-left"> Display </span>'
        + '<select class="form-control" ng-change="firstPage()" ng-model="pageSize" ng-options="size for size in pageSizeList"></select></span>'
        + '<span class="per-page"> per page </span></div>',
        link: function (scope) {

            scope.pageSizeList = [10, 50, 100, 300];
            scope.currentPage = 0;
            scope.pageSize = 10;

            scope.isFirstPage = function () {
                return scope.currentPage == 0;
            };
            scope.isLastPage = function () {
                return scope.currentPage
                    >= scope.items().length / scope.pageSize - 1;
            };
            scope.nextPage = function () {
                if (!scope.isLastPage()) {
                    scope.currentPage++;
                }
            };
            scope.previousPage = function () {
                if (!scope.isFirstPage()) {
                    scope.currentPage--;
                }
            };
            scope.firstPage = function () {
                scope.currentPage = 0;
            };
            scope.lastPage = function () {
                scope.currentPage = scope.getNbPages()-1;
            };
            scope.goToPage = function(page) {
                scope.currentPage = page;
            };
            scope.getNbPages = function () {
                return Math.ceil(scope.items().length / scope.pageSize);
            };
            scope.getPageList = function() {
                var pageList = new Array();
                for(var i=0;i<scope.getNbPages();i++) {
                    pageList.push(i+1);
                }
                return pageList;
            };
            scope.$parent.pageItems = function () {
                var start = scope.currentPage * scope.pageSize;
                var limit = scope.pageSize;
                var tmp = angular.extend([],scope.items());
                var nb_pages = Math.ceil(tmp.length / scope.pageSize);

                if(nb_pages != scope.initial_nb_pages) {
                    scope.initial_nb_pages = nb_pages;
                    scope.firstPage();
                }

                return scope.items().slice(start, start + limit);
            };

            scope.initial_nb_pages = scope.getNbPages();
        }
    };
});
