/**
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
angular
.module("starter")
.controller("CmsViewMapController", function ($log, $scope, $stateParams, Location, Cms) {
    angular.extend($scope, {
        is_loading: true,
        value_id: $stateParams.value_id
    });

    Cms.setValueId($stateParams.value_id);

    $scope.loadContent = function () {
        Cms.findBlock($stateParams.block_id, $stateParams.page_id)
        .then(function (data) {
            $scope.block = data.block;
            $scope.page_title = data.page_title;

            var title = (data.block.title) ? data.block.title : data.block.label;

            var marker = {
                title: title + '<br />' + data.block.address,
                is_centered: true
            };

            if (data.block.latitude && data.block.longitude) {
                marker.latitude = data.block.latitude;
                marker.longitude = data.block.longitude;
            } else {
                marker.address = data.block.address;
            }

            if (data.block.picture_url) {
                marker.icon = {
                    url: data.block.picture_url,
                    width: 70,
                    height: 44
                };
            }

            Location.getLocation()
            .then(function (position) {
                $scope.createMap(position.coords, marker);
            }, function () {
                $scope.createMap(null, marker);
            });
        }, function (error) {
            $log.error('[CmsViewMapController] an error occurred while loading CMS', error);
        }).then(function () {
            $scope.is_loading = false;
        });
    };

    $scope.createMap = function (origin, destination) {
        $scope.is_loading = false;

        if (origin) {
            $scope.map_config = {
                coordinates: {
                    origin: origin,
                    destination: destination
                }
            };
        } else {
            $scope.map_config = {
                markers: [destination]
            };
        }
    };

    $scope.loadContent();
});
