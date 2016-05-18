"use strict";

App.directive("sbLoadMore", function($rootScope) {

    return {
        restrict: 'A',
        scope: {
            factory: "=",
            collection: "=",
            loadMore: "&"
        },
        transclude: true,
        template:
            '<div ng-transclude></div>' +
                '<div class="padding" ng-show="is_active">' +
                    '<button id="bt_load_more" type="button" class="button" ng-click="LoadCollectionContent()">{{ "Load More" | translate }}</button>' +
                '</div>' +
                '<div class="padding" ng-show="loader_is_visible">' +
                    '<sb-loader is-loading="loader_is_visible" size="\'48\'" block="\'background\'"></sb-loader>' +
                '</div>' +
            '</div>',
        link: function (scope, element) {

            scope.loader_is_visible = false;

            if(angular.isDefined(scope.collection) && angular.isDefined(scope.factory)) {
                var collectionWatcher = scope.$watch("collection", function() {
                    scope.checkStatus(scope.collection);
                });
            }

            if(angular.isDefined(scope.factory)) {
                angular.element(element).bind("scroll", function (e) {
                    if (this.scrollHeight - this.clientHeight - this.scrollTop <= 20) {
                        if (!scope.is_active) return;
                        scope.LoadCollectionContent();
                    }
                });
            } else {
                scope.is_active = false;
            }

            scope.$on("$destroy", function() {
                angular.element(element).unbind('scroll');
                if(angular.isDefined(collectionWatcher)) {
                    collectionWatcher();
                }
            });

            scope.LoadCollectionContent = function() {

                scope.loader_is_visible = true;
                scope.is_active = false;
                var promise = scope.loadMore({offset: scope.collection.length});

                if(promise && typeof(promise) == "object") {

                    promise.then(function(response) {

                        if(response) {
                            scope.checkStatus(response.data.collection);
                        }

                        scope.loader_is_visible = false;
                    }, function() {
                        scope.loader_is_visible = false;
                    });

                } else if(!promise) {

                    scope.factory.findAll(scope.collection.length).success(function (data) {

                        angular.forEach(data.collection, function (value, key) {
                            scope.collection.push(value);
                        });

                        if(data) {
                            scope.checkStatus(data.collection);
                        }

                        scope.loader_is_visible = false;

                    }).error(function () {
                        scope.loader_is_visible = false;
                    });

                } else {
                    scope.is_active = false;
                    scope.loader_is_visible = false;
                }

            };

            scope.checkStatus = function(collection) {

                if(!collection || !collection.length) {
                    scope.is_active = false;
                } else if(!isNaN(scope.factory.displayed_per_page)) {
                    scope.is_active = collection.length >= scope.factory.displayed_per_page;
                }

                return this;

            }
        }
    }

});