/**
 * CustomerController
 *
 * This controller handles the login modal.
 *
 * @version 4.19.1
 * @author Xtraball SAS
 */
angular
    .module('starter')
    .controller('CustomerController', function ($state, $ionicHistory, $cordovaCamera, $ionicActionSheet, Loader,
                                                $ionicPopup, Customer, $ionicScrollDelegate, $rootScope, $scope, $timeout,
                                                $translate, $session, Application, Dialog, FacebookConnect,
                                                HomepageLayout, Modal, Picture, CropImage, Pages, Push, PushService) {

        /**
         * Clears out the customer object!
         * @returns {*|$scope.customer|Customer.customer|null}
         */
        $scope.pristineCustomer = function () {
            $scope.customer = {
                firstname: '',
                lastname: '',
                nickname: '',
                email: '',
                image: '',
                change_password: false,
                password: '',
                repeat_password: '',
                privacy_policy: false
            };

            return $scope.customer;
        };

        angular.extend($scope, {
            currentLanguage: 'en',
            customer: Customer.customer || $scope.pristineCustomer(),
            card: {},
            card_design: false,
            is_logged_in: Customer.isLoggedIn(),
            app_name: Application.app_name,
            display_login_form: (!$scope.is_logged_in) && (!Customer.display_account_form),
            display_account_form: ($scope.is_logged_in || Customer.display_account_form),
            display_settings: false,
            can_connect_with_facebook: !!Customer.can_connect_with_facebook,
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
                    enable_commercial_agreement_label: $translate.instant("I'd like to hear about offers & services", 'customer'),
                    enable_password_verification: false,
                }
            },
            settings: {
                push: PushService.isEnabled,
                counter: 7,
            },
            version: {
                number: $translate.instant('Latest', 'customer'),
                code: $translate.instant('latest', 'customer'),
            }
        });

        $session
            .getItem('sb-current-language')
            .then(function (value) {
                $scope.currentLanguage = (value === null) ? 'en' : value;
            }).catch(function (error) {
                $scope.currentLanguage = 'en';
            });

        if (window.IS_NATIVE_APP) {
            try {
                cordova.getAppVersion.getVersionNumber(function (versionNumber) {
                    $scope.version.number = versionNumber;
                });
                cordova.getAppVersion.getVersionCode(function (versionCode) {
                    $scope.version.code = versionCode;
                });
            } catch (e) {
                // Nope
            }
        }

        $scope.privacyPolicyField = {
            label: $translate.instant('I have read & agree the privacy policy.', 'customer'),
            value: $scope.customer.privacy_policy,
            is_required: true,
            modaltitle: $translate.instant('Privacy policy.', 'customer'),
            htmlContent: Application.gdpr.isEnabled ?
                Application.privacyPolicy.text + '<br /><br />' + Application.privacyPolicy.gdpr :
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
                        cardDesign: $scope.card_design,
                        is_loading: false,
                        page_title: $scope.privacyPolicyField.modaltitle
                    }),
                    animation: 'slide-in-right-left'
                }).then(function (modal) {
                    $scope.ppModal = modal;
                    $scope.ppModal.show();

                    return modal;
                });
        };

        $scope.closeAction = function () {
            if ($scope.display_forgot_password_form === true ||
                $scope.display_settings === true) {
                $scope.displayLoginForm();
            } else {
                Customer.closeModal();
            }
        };

        $scope.getLanguages = function () {
            return window.AVAILABLE_LANGUAGES;
        };

        $scope.getPushToken =  function () {
            let message = $translate.instant('Your device is not registered for Push notifications!', 'customer');
            if (Push.lastErrorMessage !== null && Push.device_token === null) {
                message = Push.lastErrorMessage;
            } else if (Push.device_token !== null && Push.device_token.length > 0) {
                message = Push.device_token;
            }

            return message;
        };

        $scope.devCounter = function () {
            if (window.IS_NATIVE_APP) {
                try {
                    if ($scope.settings.counter <= 0) {
                        return;
                    }
                    $scope.settings.counter--;
                    window.plugins.toast.hide();
                    window.plugins.toast.showShortBottom(
                        $translate
                            .instant('$1 more to access advanced options!', 'customer')
                            .replace('$1', $scope.settings.counter)
                    );
                } catch (e) {
                    console.error('Something went wrong while accessing advanced options!');
                }
            }
        };

        $scope.sendTestPush = function () {
            Loader.show($translate.instant('Sending...', 'customer'));
            Customer
                .sendTestPush(Push.device_token)
                .then(function (payload) {
                    // Saved!
                }, function (error) {
                    // Revert!
                }).then(function () {
                    Loader.hide();
                });
        };

        $scope.messagePushRegistration = function (success) {
            if (Push.lastErrorMessage && Push.lastErrorMessage.length) {
                if (success) {
                    Push.lastErrorMessage += "<br />" +
                        $translate.instant('Note: the registration refresh failed now, but you still have a valid push token!', 'customer');
                }
                Dialog.alert('Error', Push.lastErrorMessage, 'OK');
            } else {
                Dialog.alert('Success', 'Your are correctly registered to push!', 'OK');
            }
        };

        $scope.forcePushRegistration = function () {
            PushService.register(true);
            PushService
                .isReadyPromise
                .then(function () {
                    $scope.messagePushRegistration(true);
                }, function () {
                    $scope.messagePushRegistration(false);
                });
        };

        $scope.sendTestLocal = function () {
            PushService.sendLocalNotification(
                Date.now(),
                $translate.instant('Local notification', 'customer'),
                $translate.instant('This is a local notification test!', 'customer'));
        };

        $scope.updateSettings = function () {
            Loader.show($translate.instant('Saving...', 'customer'));
            Customer
                .saveSettings($scope.settings)
                .then(function (payload) {
                    // Saved!
                }, function (error) {
                    // Revert!
                }).then(function () {
                    Loader.hide();
                });
        };

        $scope.reloadLocale = function (select) {
            $scope.currentLanguage = select.currentLanguage;
            $session.setItem('sb-current-language', $scope.currentLanguage);
            Loader.show($translate.instant('Loading translations...', 'customer'));
            Application
                .reloadLocale($scope.currentLanguage)
                .then(function (success) {
                    $timeout(function () {
                        $translate.translations = success.translations;
                        Pages.populate(success.features);
                        // Clear history, and go home!
                        $ionicHistory
                            .clearCache()
                            .then(function() {
                                Customer.hideModal();
                                $state.reload();
                            });
                    });
                }).then(function () {
                    Loader.hide();
                });
        };

        $scope.getVersion = function () {
            if (window.IS_NATIVE_APP) {
                return $scope.version.number + ' (' + $scope.version.code + ')';
            }
            return $scope.version.number;
        };

        /**
         * Check for CHCP live updates!
         */
        $scope.checkUpdate = function () {
            $rootScope.checkForUpdate();
        };

        $scope.loginEmail = function () {
            Customer.login($scope.customer);
        };

        $scope.retrieveForgotPassword = function () {
            Customer.forgotPassword($scope.customer.email);
        };

        $scope.requestGdprToken = function () {
            Customer.requestToken();
        };

        $scope.loginFacebook = function () {
            FacebookConnect.login();
        };

        $scope.avatarUrl = function () {
            // Means the customer image was edited!
            if ($scope.customer.image &&
                $scope.customer.image.indexOf('data:') === 0) {
                return $scope.customer.image;
            }
            // Else we fetch it normally, first customer defined, then default image!
            return Customer.getAvatarUrl();
        };

        $scope.editProfilePicture = function () {
            Picture
                .takePicture()
                .then(function (result) {
                    CropImage
                        .openPopup(result.image)
                        .then(function (success) {
                            // Set new avatar!
                            $scope.customer.image = success;
                        }, function (error) {
                            // Do nothing!
                        });
                }, function (takeError) {
                    // Do nothing!
                });
        };

        $scope.loadContent = function () {
            // Loading my account settings!
            $scope.myAccount = Application.myAccount;

            if ($scope.myAccount.settings.enable_commercial_agreement_label.length <= 0) {
                $scope.myAccount.settings.enable_commercial_agreement_label =
                    $translate.instant("I'd like to hear about offers & services", 'customer');
            }

            $scope.card_design = $scope.myAccount.settings.design === 'card';

            if (!$scope.is_logged_in) {
                return;
            }

            Loader.show();

            // Force display account when logged in!
            $scope.displayAccountForm();

            $scope.customer = Customer.customer;
            $scope.customer.metadatas = _.isObject($scope.customer.metadatas) ? $scope.customer.metadatas : {};

            // @todo check relevance here, and/or optimize usage!
            // these must be moved to the front/app/init & cached, using resources for nothing!
            HomepageLayout
                .getActiveOptions()
                .then(function (options) {
                    $scope.custom_fields = [];

                    _.forEach(options, function (opt) {
                        var fields = _.get(opt, 'custom_fields');

                        if (_.isArray(fields) && fields.length > 0) {
                            $scope.custom_fields.push(_.pick(opt, ['name', 'code', 'custom_fields']));
                            _.forEach(fields, function (field) {
                                var mpath = opt.code + '.' + field.key;
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

        $scope.registerOrSave = function () {
            Loader.show();

            if ($scope.myAccount.settings.enable_password_verification &&
                ($scope.customer.password !== $scope.customer.repeat_password)) {
                Loader.hide();
                Dialog.alert('Error', 'Passwords do not match!', 'OK', -1, 'customer');
                return;
            }

            Customer
                .save($scope.customer)
                .then(function (success) {
                    Dialog
                        .alert('Account', success.message, 'OK', -1, 'customer')
                        .then(function () {
                            Customer.closeModal();
                        });
                    return success;
                }, function (error) {
                    Dialog.alert('Error', error.message, 'OK', -1);
                }).then(function () {
                    Loader.hide();
                });
        };

        $scope.logout = function () {
            Dialog
                .confirm('Confirmation', 'Are you sure you want to log out?', ['YES', 'NO'], '', 'customer')
                .then(function (result) {
                    if (result) {
                        Customer
                            .logout()
                            .then(function (data) {
                                if (data.success) {
                                    FacebookConnect.logout();
                                    $scope.pristineCustomer();
                                    Customer.hideModal();
                                }
                            });
                    }
                });
        };

        $scope.willShowSettings = function () {
            return window.IS_NATIVE_APP;
        };

        $scope.copyTokenToClipboard = function () {
            // Only for native for now!
            if (window.IS_NATIVE_APP) {
                try {
                    cordova.plugins.clipboard.copy(Push.device_token);
                    window.plugins.toast.showShortCenter($translate.instant('Token copied to clipboard!', 'customer'));
                } catch (e) {
                    console.error('Something went wrong while copiyng token to clipboard!');
                }
            }
        };

        $scope.appSettings = function () {
            if ($scope.display_settings === true) {
                $scope.displayLoginForm();
                return;
            }
            $scope.scrollTop();
            $scope.display_forgot_password_form = false;
            $scope.display_account_form = false;
            $scope.display_login_form = false;
            $scope.display_settings = true;
        };

        $scope.displayLoginForm = function () {
            if (Customer.isLoggedIn()) {
                $scope.displayAccountForm();
                return;
            }
            $scope.scrollTop();
            $scope.display_forgot_password_form = false;
            $scope.display_account_form = false;
            $scope.display_settings = false;
            $scope.display_login_form = true;
        };

        // Keep it for forgot password event!
        $rootScope.$on('displayLogin', function () {
            $scope.displayLoginForm();
        });

        $scope.displayForgotPasswordForm = function () {
            $scope.scrollTop();
            $scope.display_login_form = false;
            $scope.display_account_form = false;
            $scope.display_settings = false;
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
            $scope.display_settings = false;
            $scope.display_forgot_password_form = false;
            $scope.display_account_form = true;
        };

        $scope.scrollTop = function () {
            $ionicScrollDelegate.scrollTop(false);
        };

        $scope.removeCreditCard = function () {
            Dialog
                .confirm('Confirmation', 'Do you confirm you want to remove your card?')
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
