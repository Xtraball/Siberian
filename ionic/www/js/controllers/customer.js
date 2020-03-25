/**
 * CustomerController
 *
 * This controller handles the login modal.
 *
 * @version 4.18.12
 * @author Xtraball SAS
 */
angular
    .module("starter")
    .controller("CustomerController", function($cordovaCamera, $ionicActionSheet, Loader,
                                              $ionicPopup, Customer, $ionicScrollDelegate, $rootScope, $scope, $timeout,
                                              $translate, Application, Dialog, FacebookConnect,
                                              HomepageLayout, Modal) {

    $scope.resetCustomer = function () {
        $scope.customer = {
            firstname: '',
            lastname: '',
            nickname: '',
            email: '',
            change_password: false,
            password: '',
            privacy_policy: false
        };

        return $scope.customer;
    };

    angular.extend($scope, {
        customer: Customer.customer || $scope.resetCustomer(),
        card: {},
        card_design: false,
        is_logged_in: Customer.isLoggedIn(),
        app_name: Application.app_name,
        display_login_form: (!$scope.is_logged_in) && (!Customer.display_account_form),
        display_account_form: ($scope.is_logged_in || Customer.display_account_form),
        can_connect_with_facebook: !!Customer.can_connect_with_facebook,
        show_avatar: true,
        avatar_loaded: false,
        privacy_policy: Application.privacyPolicy.text,
        privacy_policy_gdpr: Application.privacyPolicy.gdpr,
        gdpr: {
            isEnabled: Application.gdpr.isEnabled
        },
        myAccount: {
            title: $translate.instant('My account', 'customer'),
            settings: {
                enable_facebook_login: true,
                enable_registration: true,
                enable_commercial_agreement: true,
                enable_commercial_agreement_label: $translate.instant("I'd like to hear about offers & services", 'customer')
            }
        }
    });

    $scope.privacyPolicyField = {
        label: $translate.instant('I have read & agree the privacy policy.', 'customer'),
        value: $scope.customer.privacy_policy,
        is_required: true,
        modaltitle: $translate.instant('Privacy policy.', 'customer'),
        htmlContent: Application.gdpr.isEnabled ?
            Application.privacyPolicy.text + '<br /><br />' + Application.privacyPolicy.gdpr:
            Application.privacyPolicy.text
    };

    $scope.ppModal = null;
    $scope.showPrivacyPolicy = function () {
        Modal
            .fromTemplateUrl('templates/cms/privacypolicy/l1/privacy-policy-modal.html', {
                scope: angular.extend($scope, {
                    close: function () {
                        $scope.ppModal.hide();
                    },
                    is_loading: false,
                    page_title: $scope.privacyPolicyField.modaltitle
                }),
                animation: 'slide-in-up'
            }).then(function (modal) {
                $scope.ppModal = modal;
                $scope.ppModal.show();

                return modal;
            });
    };

    $scope.closeAction = function () {
        if ($scope.display_forgot_password_form === true) {
            $scope.displayLoginForm();
        } else {
            $scope._pcustomer_close();
        }
    };

    // Alias for the global login modal!
    $scope.login = function () {
        Customer.loginModal($scope);
    };

    $scope.requestToken = function () {
        Customer.requestToken();
    };

    $scope.loginWithFacebook = function () {
        if ($rootScope.isNotAvailableInOverview()) {
            return;
        }
        FacebookConnect.login();
    };

    $scope.hideAvatar = function () {
        $scope.show_avatar = false;
    };

    $scope.avatarLoaded = function () {
        $scope.avatar_loaded = true;
        $scope.show_avatar = true;
    };

    $scope.editAvatar = function () {
        var buttons = [
            {
                text: $translate.instant("Edit")
            }
        ];

        if ($scope.customer.avatar !== null) {
            var text = 'Cancel ' + ($scope.customer.delete_avatar ? 'delete' : 'edit');
            buttons.push({ text: $translate.instant(text) });
        } else {
            if ($scope.customer.is_custom_image) {
                buttons.push({ text: $translate.instant('Delete') });
            }
        }

        var hideSheet = $ionicActionSheet.show({
            buttons: buttons,
            cancelText: $translate.instant('Cancel'),
            cancel: function () {
                hideSheet();
            },
            buttonClicked: function (index) {
                if (index == 0) {
                    // We have to use timeout, if we do not,
                    // next action sheet will loose focus after 400ms
                    // because of the closing one. For more details,
                    // see this : https://github.com/driftyco/ionic/blob/1.x/js/angular/service/actionSheet.js#L138
                    $timeout($scope.takePicture, 600);
                }
                if (index == 1) {
                    if ($scope.customer.avatar != null) {
                        // Cancel edit/delete :
                        $scope.customer.avatar = null;
                        $scope.customer.delete_avatar = false;
                        $scope.avatar_url = Customer.getAvatarUrl($scope.customer.id);
                    } else {
                        $scope.customer.avatar = false;
                        $scope.customer.delete_avatar = true;
                        $scope.avatar_url = Customer.getAvatarUrl($scope.customer.id, {ignore_stored: true});
                    }

                    $rootScope.$broadcast(SB.EVENTS.AUTH.editSuccess);
                }
                return true;
            }
        });
    };


    /**
     *
     * @todo move me to Picture service, add the cool crop modal option
     *
     * @param field
     */
    $scope.takePicture = function (field) {
        var gotImage = function (image_url) {
            // TODO: move all picture taking and cropping modal
            // into a dedicated service for consistence against modules
            $scope.cropModal = {original: image_url, result: null};

            // DO NOT REMOVE popupShowing !!!
            // img-crop directive doesn't work if it has been loaded off screen
            // We show the popup, then switch popupShowing to true, to add
            // img-crop in the view.
            $scope.popupShowing = false;
            $ionicPopup.show({
                template: '<div style="position: absolute" class="cropper">' +
                    '<img-crop ng-if="popupShowing" image="cropModal.original" result-image="cropModal.result" area-type="square" result-image-size="256" result-image-format="image/jpeg" result-image-quality="0.9"></img-crop>' +
                '</div>',
                cssClass: 'avatar-crop',
                scope: $scope,
                buttons: [{
                  text: $translate.instant('Cancel'),
                  type: 'button-default',
                  onTap: function(e) {
                      return false;
                  }
                }, {
                  text: $translate.instant('OK'),
                  type: 'button-positive',
                  onTap: function(e) {
                    return true;
                  }
                }]
            }).then(function(result) {
                if(result) {
                    $scope.cropModalCtrl = null;
                    $scope.avatar_url = $scope.cropModal.result;
                    $scope.customer.avatar = $scope.cropModal.result;
                    $scope.customer.delete_avatar = false;
                }
            });
            $scope.popupShowing = true;
        };

        var gotError = function (err) {
            // An error occured. Show a message to the user
        };

        if (Application.is_webview) {
            var input = angular.element("<input type='file' accept='image/*'>");
            var selectedFile = function (evt) {
                var file=evt.currentTarget.files[0];
                var reader = new FileReader();
                reader.onload = function (onloadEvt) {
                    gotImage(onloadEvt.target.result);
                    input.off('change', selectedFile);
                };
                reader.onerror = gotError;
                reader.readAsDataURL(file);
            };
            input.on('change', selectedFile);
            input[0].click();
        } else {
            var source_type = Camera.PictureSourceType.CAMERA;

            // Show the action sheet
            var hideSheet = $ionicActionSheet.show({
                buttons: [
                    { text: $translate.instant('Take a picture') },
                    { text: $translate.instant('Import from Library') }
                ],
                cancelText: $translate.instant('Cancel'),
                cancel: function () {
                    hideSheet();
                },
                buttonClicked: function (index) {
                    if (index == 0) {
                        source_type = Camera.PictureSourceType.CAMERA;
                    }
                    if (index == 1) {
                        source_type = Camera.PictureSourceType.PHOTOLIBRARY;
                    }

                    var options = {
                        quality: 90,
                        destinationType: Camera.DestinationType.DATA_URL,
                        sourceType: source_type,
                        encodingType: Camera.EncodingType.JPEG,
                        targetWidth: 256,
                        targetHeight: 256,
                        correctOrientation: true,
                        popoverOptions: CameraPopoverOptions,
                        saveToPhotoAlbum: false
                    };

                    $cordovaCamera
                        .getPicture(options)
                        .then(function (imageData) {
                            gotImage('data:image/jpeg;base64,' + imageData);
                        }, gotError);

                    return true;
                }
            });
        }
    };

    $scope.toggleCardDesign = function () {
        $scope.card_design = !$scope.card_design;
    };

    $scope.loadContent = function () {
        // Loading my account settings!
        $scope.myAccount = Application.myAccount;

        if ($scope.myAccount.settings.enable_commercial_agreement_label.length <= 0) {
            $scope.myAccount.settings.enable_commercial_agreement_label = $translate.instant("I'd like to hear about offers & services", 'customer');
        }

        $scope.card_design = $scope.myAccount.settings.design === 'card';

        if (!$scope.is_logged_in) {
            return;
        }

        // Force display account when logged in!
        $scope.displayAccountForm();
        Loader.show();

        $scope.customer = Customer.customer;
        $scope.customer.metadatas = _.isObject($scope.customer.metadatas) ? $scope.customer.metadatas : {};
        $scope.avatar_url = Customer.getAvatarUrl($scope.customer.id);

        HomepageLayout
            .getActiveOptions()
            .then(function (options) {
                $scope.optional_fields = {
                    ranking: !!_.find(options, {
                        use_ranking: '1'
                    }),
                    nickname: !!_.find(options, {
                        use_nickname: '1'
                    })
                };

                $scope.custom_fields = [];

                _.forEach(options, function (opt) {
                    var fields = _.get(opt, 'custom_fields');

                    if (_.isArray(fields) && fields.length > 0) {
                        $scope.custom_fields.push(_.pick(opt, ['name', 'code', 'custom_fields']));
                        _.forEach(fields, function (field) {
                            var mpath =  opt.code + '.' + field.key;
                            _.set(
                                $scope.customer.metadatas,
                                mpath,
                                _.get($scope.customer.metadatas, mpath, (field.default || null))
                            );
                        });
                    }
                });

                Loader.hide();
            });
    };

    $scope.save = function () {
        $scope.is_loading = true;

        Loader.show();

        Customer.save($scope.customer)
            .then(function (data) {
                if (angular.isDefined(data.message)) {
                    Dialog.alert('', data.message, 'OK', -1)
                        .then(function () {
                            Customer.login_modal.hide();
                        });
                }

                return data;
            }, function (data) {
                if (data && angular.isDefined(data.message)) {
                    Dialog.alert('Error', data.message, 'OK', -1);
                }

                return data;
            }).then(function () {
                $scope.is_loading = false;

                Loader.hide();
            });
    };

    $scope.logout = function () {
        Customer
            .logout()
            .then(function (data) {
                FacebookConnect.logout();
                if (data.success) {
                    $scope.resetCustomer();
                    Customer.hideModal();
                }
            });
    };

    $scope.displayLoginForm = function () {
        $scope.scrollTop();
        $scope.display_forgot_password_form = false;
        $scope.display_account_form = false;
        $scope.display_privacy_policy = false;
        $scope.display_login_form = true;
    };

    $rootScope.$on('displayLogin', function () {
        $scope.displayLoginForm();
    });

    $scope.displayForgotPasswordForm = function () {
        $scope.scrollTop();
        $scope.display_login_form = false;
        $scope.display_account_form = false;
        $scope.display_privacy_policy = false;
        $scope.display_forgot_password_form = true;
    };

    $scope.displayAccountForm = function () {
        $scope.scrollTop();
        if (!$scope.myAccount &&
            !$scope.myAccount.settings &&
            !$scope.myAccount.settings.enable_registration) {
            $scope.displayLoginForm();
        }
        $scope.display_login_form = false;
        $scope.display_forgot_password_form = false;
        $scope.display_privacy_policy = false;
        $scope.display_account_form = true;
    };

    $scope.scrollTop = function () {
        $ionicScrollDelegate.scrollTop(false);
    };

    $scope.unloadcard = function() {
        Dialog.confirm('Confirmation', 'Do you confirm you want to remove your card?')
            .then(function (result) {
                if (result) {
                    $scope.is_loading = true;

                    Loader.show();

                    // We cannot be there without customer!
                    Customer.removeCard()
                        .then(function (data) {
                            $scope.card = {};
                            $scope.customer.stripe = {};
                        }, function (data) {
                            if (data && angular.isDefined(data.message)) {
                                Dialog.alert('Error', data.message, 'OK', -1);
                            }
                        }).then(function () {
                            $scope.is_loading = false;
                            Loader.hide();
                        });
                }
            });
    };

    $scope.loadContent();
});
