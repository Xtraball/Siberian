/**
 * CustomerController
 *
 * This controller handles the login modal.
 *
 * @version 5.0.0
 * @author Xtraball SAS
 */
angular
    .module('starter')
    .controller('CustomerController', function ($state, $ionicHistory, $cordovaCamera, $ionicActionSheet, Loader,
                                                $ionicPopup, Customer, $ionicScrollDelegate, $rootScope, $scope, $timeout,
                                                $translate, $session, Application, Dialog, $window,
                                                HomepageLayout, Modal, Picture, CropImage, Pages) {

        /**
         * Clears out the customer object!
         * @returns {*|$scope.customer|Customer.customer|null}
         */
        $scope.pristineCustomer = function () {
            return Customer.pristineCustomer();
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
            privacy_policy: Application.privacyPolicy.text,
            privacy_policy_gdpr: Application.privacyPolicy.gdpr,
            XS_VERSION: XS_VERSION,
            gdpr: {
                isEnabled: Application.gdpr.isEnabled
            },
            myAccount: {
                title: $translate.instant('My account', 'customer'),
                settings: {
                    enable_registration: true,
                    enable_commercial_agreement: true,
                    enable_commercial_agreement_label: $translate.instant("I'd like to hear about offers & services", 'customer'),
                    enable_password_verification: false,
                }
            },
            settings: {
                //push: PushService.isEnabled,
                push: true,
                counter: 3,
                push_token: $translate.instant('Your device is not registered for Push notifications!', 'customer'),
            },
            version: {
                number: $translate.instant('Latest', 'customer'),
                code: $translate.instant('latest', 'customer'),
            },
            /** Variants for chrome */
            user: {
                email: '',
                password: ''
            }
        });

        $scope.i18n = {
            localBirthdateTitle: $translate.instant('Birthdate', 'customer')
        };

        $scope.backButtonIcon = function () {
            return Application.getBackIcon();
        };

        $session
            .getItem('sb-current-language')
            .then(function (value) {
                $scope.currentLanguage = (value === null) ? 'en' : value;
            }).catch(function (error) {
                $scope.currentLanguage = 'en';
            });

        if (window.IS_NATIVE_APP) {

            try {
                $window.plugins.OneSignal.getDeviceState(function(stateChanges) {
                    $scope.settings.push_token = stateChanges.pushToken;
                });
            } catch (e) {
                // Nope
            }

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

        $scope.rndName = function (length) {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() *
                    charactersLength));
            }
            return result;
        };
        $scope.rndEmail = $scope.rndName(20);
        $scope.rndOldPassword = $scope.rndName(20);
        $scope.rndPassword = $scope.rndName(20);
        $scope.rndPasswordRepeat = $scope.rndName(20);

        $scope.displayField = function (field) {
            var bDisplay = (field.type !== 'spacer' && $scope.card_design) || !$scope.card_design;
            if (!bDisplay) {
                return false;
            }

            if (field.when && field.when === 'guest' && Customer.isLoggedIn()) {
                return false;
            }

            if (field.when && field.when === 'customer' && !Customer.isLoggedIn()) {
                return false;
            }

            return true;
        };

        $scope.deleteAccount = function () {
            Dialog
                .confirm(
                    'Attention',
                    'Your are about to delete your account, this action can not be reverted<br />Please confirm!', ['YES, DELETE', 'NO, GO BACK'], '', 'customer')
                .then(function (result) {
                    if (result) {
                        // Delete account!
                        Loader.show($translate.instant('Please wait...', 'customer'));
                        Customer
                            .deleteAccount()
                            .then(function (payload) {
                                // Also forces logout
                                Loader.hide();
                                Customer.logout();
                                //
                                Dialog.alert(undefined, payload.message, 'OK', -1, 'customer');
                                // Removed!
                            }, function (error) {
                                Dialog.alert('Error', error.message, 'OK', -1, 'customer');
                                // Revert!
                            }).then(function () {
                            Loader.hide();
                        });
                    }
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
            $window.plugins.OneSignal.getDeviceState(function(stateChanges) {
                Customer
                    .sendTestPush(stateChanges.userId)
                    .then(function (payload) {
                        // Saved!
                    }, function (error) {
                        // Revert!
                    }).then(function () {
                        Loader.hide();
                    });
            });

        };

        $scope.messagePushRegistration = function (success) {
            Dialog.alert('N.A.', 'N.A.', 'OK');
            //if (Push.lastErrorMessage && Push.lastErrorMessage.length) {
            //    if (success) {
            //        Push.lastErrorMessage += "<br />" +
            //            $translate.instant('Note: the registration refresh failed now, but you still have a valid push token!', 'customer');
            //    }
            //    Dialog.alert('Error', Push.lastErrorMessage, 'OK');
            //} else {
            //    Dialog.alert('Success', 'Your are correctly registered to push!', 'OK');
            //}
        };

        $scope.forcePushRegistration = function () {

            $window.plugins.OneSignal.promptForPushNotificationsWithUserResponse(function(accepted) {
                console.log("User accepted notifications: " + accepted);
                alert("User accepted notifications: " + accepted);
            });

            //PushService.register(true);
            //PushService
            //    .isReadyPromise
            //    .then(function () {
            //        $scope.messagePushRegistration(true);
            //    }, function () {
            //        $scope.messagePushRegistration(false);
            //    });
        };

        $scope.sendTestLocal = function () {
            //PushService.sendLocalNotification(
            //    Date.now(),
            //    $translate.instant('Local notification', 'customer'),
            //    $translate.instant('This is a local notification test!', 'customer'));
        };

        $scope.updateSettings = function () {
            Loader.show($translate.instant('Saving...', 'customer'));
            Customer
                .saveSettings($scope.settings)
                .then(function (payload) {
                    // Saved! and update local cache
                    //PushService.isEnabled = $scope.settings.push;
                }, function (error) {
                    // Revert!
                    $scope.settings.push = !$scope.settings.push;
                }).then(function () {
                    Loader.hide();
                });
        };

        $scope.reloadLocale = function (select) {
            $scope.currentLanguage = select.currentLanguage;

            // Save in session
            $session.setItem('sb-current-language', $scope.currentLanguage);

            // Update momentjs
            var tmpLang = $scope.currentLanguage.replace('_', '-').toLowerCase();
            var langPriority = [tmpLang, tmpLang.split('-')[0], 'en'];
            moment.locale(langPriority);

            // Reload from server
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
                            .then(function () {
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

        $scope.loginEmail = function () {
            var hooksBeforeLogin = ['customer.before.login'];
            var hooksAfterLogin = ['customer.after.login'];
            var hooksAfterLoginError = ['customer.after.login.error'];

            Customer
                .runHooks(hooksBeforeLogin, $scope.customer)
                .then(function () {
                    Customer
                        .login($scope.customer)
                        .then(function(success) {
                            Customer.runHooks(hooksAfterLogin, {customer: $scope.customer, success: success});
                        }, function(error) {
                            Customer.runHooks(hooksAfterLoginError, {customer: $scope.customer, error: error});
                        });
                }, function (error) {
                    console.log('runHooks', error);
                    Dialog.alert('Error', error.message, 'OK', -1);
                });
        };

        $scope.retrieveForgotPassword = function () {
            Customer.forgotPassword($scope.customer.email);
        };

        $scope.requestGdprToken = function () {
            Customer.requestToken();
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

        $scope.checkPasswordStatus =  function () {
            if (!$scope.customer.change_password) {
                $timeout(function () {
                    $scope.customer.old_password = '';
                    $scope.customer.password = '';
                    $scope.customer.repeat_password = '';
                });
            }
        };

        $scope.checkMobile =  function () {
            if ($scope.myAccount.settings.use_mobile ||
                $scope.myAccount.settings.extra_mobile) {
                var iti = Customer.getIti('customer_mobile');

                if (!iti) { // in case it is not init
                    return true;
                }
                // trim spaces

                if (!iti.isValidNumber()) {
                    return false;
                }

                // Apply valid formatted number to the customer scope
                $scope.customer.mobile = iti.getNumber();
            }
            // in case it's not configured
            return true;
        };

        $scope.registerOrSave = function () {
            Loader.show();

            if ($scope.myAccount.settings.enable_password_verification &&
                ($scope.customer.password !== $scope.customer.repeat_password)) {
                Loader.hide();
                Dialog.alert('Error', 'Passwords do not match!', 'OK', -1, 'customer');
                return;
            }

            $scope.checkPasswordStatus();
            if (!$scope.checkMobile()) {
                Loader.hide();
                Dialog.alert('Error', 'Your mobile number is not valid!', 'OK', -1, 'customer');
                return;
            }

            var hooksBefore = [];
            var hooksAfter = [];
            var hooksAfterError = [];
            if (Customer.isLoggedIn()) {
                hooksBefore.push('customer.before.update');
                hooksAfter.push('customer.after.update');
                hooksAfterError.push('customer.after.update.error');
            } else {
                hooksBefore.push('customer.before.register');
                hooksAfter.push('customer.after.register');
                hooksAfterError.push('customer.after.register.error');
            }

            Customer
                .runHooks(hooksBefore, {customer: $scope.customer, settings: $scope.myAccount.settings})
                .then(function () {
                    Customer
                        .save($scope.customer)
                        .then(function (success) {
                            Customer
                                .runHooks(hooksAfter, {customer: $scope.customer, success: success})
                                .then(function () {
                                    Loader.hide();
                                    Dialog
                                        .alert('Account', success.message, 'OK', -1, 'customer')
                                        .then(function () {
                                            Customer.closeModal();
                                        });
                                }, function (error) {
                                    Loader.hide();
                                    Dialog.alert('Error', error.message, 'OK', -1);
                                });

                            return success;
                        }, function (error) {
                            Customer
                                .runHooks(hooksAfterError, {customer: $scope.customer, error: error})
                                .then(function () {
                                    Loader.hide();
                                    Dialog.alert('Error', error.message, 'OK', -1);
                                }, function (error) {
                                    Loader.hide();
                                    Dialog.alert('Error', error.message, 'OK', -1);
                                });
                        }).then(function () {
                            Loader.hide();
                        });
                }, function (error) {
                    Loader.hide();
                    Dialog.alert('Error', error, 'OK', -1);
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
                                    Customer.hideModal();

                                    // Reset!
                                    $scope.is_logged_in = Customer.isLoggedIn();
                                    $scope.customer = Customer.customer || $scope.pristineCustomer();
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
                    cordova.plugins.clipboard.copy($scope.settings.push_token);
                    window.plugins.toast.showShortCenter($translate.instant('Token copied to clipboard!', 'customer'));
                } catch (e) {
                    console.error('Something went wrong while copying token to clipboard!');
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

            if ($scope.myAccount.settings.use_mobile ||
                $scope.myAccount.settings.extra_mobile) {

                let defaultFallbackLanguage = CURRENT_LANGUAGE;
                Customer
                    .getIpInfo()
                    .then(function (payload) {

                        if (payload &&
                            payload.hasOwnProperty('country') &&
                            payload.country.length > 1) {
                            Customer.initIti('customer_mobile', payload.country);
                        } else {
                            Customer.initIti('customer_mobile', defaultFallbackLanguage);
                        }
                    }, function (error) {
                        Customer.initIti('customer_mobile', CURRENT_LANGUAGE);
                    });
            }
        };

        $scope.scrollTop = function () {
            $ionicScrollDelegate.scrollTop(false);
        };

        $scope.removeCreditCard = function () {
            Dialog
                .confirm('Confirmation', 'Do you confirm you want to remove your card?', ['Yes, delete!', 'No, go back!'], '', 'customer')
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
