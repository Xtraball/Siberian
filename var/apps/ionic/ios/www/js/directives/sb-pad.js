App.directive('sbPad', function($sbhttp, Url) {
    return {
        restrict: 'E',
        scope: {
            card: "="
        },
        controller: function($scope, $ionicModal) {

            $ionicModal.fromTemplateUrl('templates/loyaltycard/l1/pad.html', {
                scope: $scope,
                animation: 'slide-in-up'
            }).then(function(modal) {
                $scope.modal = modal;
            });

            $scope.openPad = function() {
                $scope.modal.show();
            };
            $scope.closeModal = function() {
                $scope.modal.hide();
            };
            //Cleanup the modal when we're done with it!
            $scope.$on('$destroy', function() {
                $scope.modal.remove();
            });
            // Execute action on hide modal
            $scope.$on('modal.hidden', function() {
                // Execute action
            });
            // Execute action on remove modal
            $scope.$on('modal.removed', function() {
                // Execute action
            });

        }
    }
});
