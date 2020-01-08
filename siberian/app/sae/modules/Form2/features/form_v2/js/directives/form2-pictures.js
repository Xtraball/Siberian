/**
 * @directive form2-pictures
 * @author Xtraball SAS
 * @version 4.18.5
 */
angular
    .module('starter')
    .directive('form2Pictures', function () {
        return {
            restrict: 'E',
            replace: false,
            scope: {
                field: '=',
                cardDesign: '=?'
            },
            templateUrl: './features/form_v2/assets/templates/l1/directive/pictures.html',
            link: function (scope) {
                scope.locationIsLoading = false;
                if (scope.cardDesign === undefined) {
                    scope.cardDesign = false;
                }

                scope.addPictureText = 'Add a picture';
                if (scope.field.image_addpicture.length > 0) {
                    scope.addPictureText = scope.field.image_addpicture;
                }
                scope.addAnotherPictureText = 'Add another picture';
                if (scope.field.image_addanotherpicture.length > 0) {
                    scope.addAnotherPictureText = scope.field.image_addanotherpicture;
                }
            },
            controller: function($scope, Picture) {

                $scope.takePicture = function () {
                    if ($scope.field.value.length >= $scope.field.limit) {
                        // Nope, can't add more! (but input is normally already disabled)
                        return;
                    }

                    Picture
                        .takePicture()
                        .then(function (success) {
                            $scope.addPicture(success.image);
                        });
                };

                $scope.addPicture = function (picture) {
                    if (!$scope.field.value) {
                        $scope.field.value = [];
                    }
                    $scope.field.value.push(picture);
                };

                $scope.removePicture = function (index) {
                    $scope.field.value.splice(index, 1);
                };
            }
        };
    });