/**
 * fanwallPictures
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .directive('fanwallPictures', function () {
        return {
            restrict: 'E',
            replace: false,
            scope: {
                form: '=',
                cardDesign: '=?'
            },
            templateUrl: './features/fanwall2/assets/templates/l1/directives/pictures.html',
            link: function (scope) {
                if (scope.cardDesign === undefined) {
                    scope.cardDesign = false;
                }

                scope.addPictureText = 'Add a photo';
                if (scope.form.addpicture &&
                    scope.form.addpicture.length > 0) {
                    scope.addPictureText = scope.form.image_addpicture;
                }
                scope.addAnotherPictureText = 'Add another photo';
                if (scope.form.addanotherpicture &&
                    scope.form.addanotherpicture.length > 0) {
                    scope.addAnotherPictureText = scope.form.image_addanotherpicture;
                }
            },
            controller: function($scope, Picture) {

                $scope.takePicture = function () {
                    if ($scope.form.pictures.length >= $scope.form.limit) {
                        // Nope, can't add more! (but input is normally already disabled)
                        console.log('[takePicture] is disabled.');
                        return;
                    }

                    Picture
                        .takePicture()
                        .then(function (success) {
                            $scope.addPicture(success.image);
                        });
                };

                $scope.addPicture = function (picture) {
                    if (!$scope.form.pictures) {
                        $scope.form.pictures = [];
                    }
                    $scope.form.pictures.push(picture);
                };

                $scope.removePicture = function (index) {
                    $scope.form.pictures.splice(index, 1);
                };

                $scope.picturePreview = function (picture) {
                    // Empty image
                    if (picture.indexOf('/') === 0) {
                        return IMAGE_URL + 'images/application' + picture;
                    }
                    return picture;
                };
            }
        };
    });
