/**
 * Discount
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 *
 */
angular
    .module("starter")
    .controller("DiscountViewController", function ($ionicHistory, Modal, $rootScope, $scope, $state, $stateParams,
                                                    $timeout, $translate, $window, Application, Customer, Dialog,
                                                    Discount, Url, SB, SocialSharing, Loader) {
        angular.extend($scope, {
            is_loading: false,
            valueId: $stateParams.value_id,
            is_logged_in: Customer.isLoggedIn(),
            cardDesign: false,
            use_pull_to_refresh: false,
            sharingIsActive: false
        });

        $scope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
            $scope.is_logged_in = true;
            $scope.loadContent();
        });

        $scope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
            $scope.is_logged_in = false;
            $scope.loadContent();
        });

        Discount.setValueId($stateParams.value_id);

        $scope.loadContent = function () {
            Discount.find($stateParams.promotion_id)
                .then(function (data) {
                    $scope.promotion = data.promotion;
                    $scope.modal_title = data.confirm_message;
                    $scope.tc_id = data.tc_id;
                    $scope.sharingIsActive = (data.social_sharing_is_active && isNativeApp);
                    $scope.page_title = data.page_title;
                }).then(function () {
                $scope.is_loading = false;
            });
        };

        $scope.share = function () {
            SocialSharing.share();
        };

        $scope.login = function () {
            Customer.loginModal($scope);
        };

        $scope.confirmBeforeUse = function () {
            if ($rootScope.isNotAvailableInOverview()) {
                return;
            }

            var buttons = ['Yes', 'No'];

            Dialog.confirm('Confirmation', $scope.modal_title, buttons, 'text-center')
                .then(function (result) {
                    if (result) {
                        $scope.use();
                    }
                });
        };

        $scope.use = function () {
            Loader.show();

            Discount
            .use($stateParams.promotion_id)
            .then(function (data) {
                Dialog.alert('Thank you', data.message, 'OK', -1)
                    .then(function () {
                        if (data.remove) {
                            Discount.findAll(true)
                                .then(function () {
                                    $ionicHistory.goBack();
                                });
                        }
                    });
            }, function (data) {
                Dialog.alert('Error', data.message, 'OK', -1)
                    .then(function () {
                        if (data.remove) {
                            Discount.findAll(true)
                                .then(function () {
                                    $ionicHistory.goBack();
                                });
                        }
                    });
            })
            .then(function () {
                Loader.hide();
            });
        };

        $scope.showTc = function () {
            $state.go('tc-view', {
                tc_id: $scope.tc_id
            });
        };

        $scope.loadContent();
    });
