/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.16.13
 */
angular
.module("starter")
.controller("FanwallMapController", function ($scope, $pwaRequest, $stateParams, Location, $state, FanwallPost) {
    angular.extend($scope, {
        isLoading: true,
        value_id: $stateParams.value_id,
        cardDesign: false
    });

    FanwallPost.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        FanwallPost
        .findAllLocation()
        .then(function (data) {
            $scope.page_title = data.page_title;
            $scope.collection = data.collection;

            var markers = [];

            for (var i = 0; i < $scope.collection.length; i++) {
                var post = $scope.collection[i];

                if (post.latitude && post.longitude) {
                    var marker = {
                        title: post.text,
                        link: $state.href('newswall-view', {
                            value_id: $scope.value_id,
                            comment_id: post.comment_id
                        })
                    };

                    marker.latitude = post.latitude;
                    marker.longitude = post.longitude;

                    if (post.image) {
                        marker.icon = {
                            url: post.image,
                            width: 70,
                            height: 44
                        };
                    }

                    markers.push(marker);
                }
            }

            $scope.map_config = {
                markers: markers,
                bounds_to_marker: true
            };

            $scope.isLoading = false;
        }, function () {
            $scope.isLoading = false;
        });
    };

    $scope.loadContent();
});