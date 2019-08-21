angular
.module("starter")
.directive("fanwallBlockedUserItem", function ($stateParams, $rootScope, $translate, $q,
                                               FanwallPost, FanwallUtils, Popover) {
        return {
            restrict: 'E',
            templateUrl: "features/fanwall2/assets/templates/l1/modal/profile/blocked-user-item.html",
            controller: function ($scope) {
                $scope.actionsPopover = null;
                $scope.popoverItems = [];

                FanwallPost.setValueId($stateParams.value_id);

                $scope.customerFullname = function () {
                    return $scope.item.firstname + " " + $scope.item.lastname;
                };

                $scope.customerImagePath = function () {
                    // Empty image
                    if ($scope.item.image.length <= 0) {
                        return "./features/fanwall2/assets/templates/images/customer-placeholder.png";
                    }
                    return IMAGE_URL + "images/customer" + $scope.item.image;
                };

                $scope.unblockUser = function () {
                    FanwallUtils
                    .unblockUser($scope.item.id, "from-user")
                    .then(function () {
                        $rootScope.$broadcast("fanwall.blockedUsers.refresh");
                    });
                };

                // Popover actions!
                $scope.openActions = function ($event) {
                    $scope
                    .closeActions()
                    .then(function () {
                        Popover
                        .fromTemplateUrl("features/fanwall2/assets/templates/l1/tabs/directives/actions-popover.html", {
                            scope: $scope
                        }).then (function (popover) {
                            $scope.actionsPopover = popover;
                            $scope.actionsPopover.show($event);
                        });
                    });
                };

                $scope.closeActions = function () {
                    try {
                        if ($scope.actionsPopover) {
                            return $scope.actionsPopover.hide();
                        }
                    } catch (e) {
                        // We skip!
                    }

                    return $q.resolve();
                };

                /**
                 *
                 */
                $scope.buildPopoverItems = function () {
                    $scope.popoverItems = [];

                    $scope.popoverItems.push({
                        label: $translate.instant("Unblock user", "fanwall"),
                        icon: "icon ion-android-remove-circle",
                        click: function () {
                            $scope
                            .closeActions()
                            .then(function () {
                                $scope.unblockUser();
                            });
                        }
                    });
                };

                // Build items!
                $scope.buildPopoverItems();
            }
        };
    });


