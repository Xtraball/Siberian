angular.module("starter").directive('sbPad', function() {
    return {
        restrict: 'E',
        scope: {
            card: "="
        },
        controller: function($scope, Modal) {

            Modal
                .fromTemplateUrl("templates/loyaltycard/l1/pad.html", {
                    scope: $scope
                }).then(function(modal) {
                    $scope.modal = modal;
                });

            $scope.openPad = function() {
                $scope.modal.show();
            };
            $scope.closeModal = function() {
                $scope.modal.hide();
            };

        }
    }
});
