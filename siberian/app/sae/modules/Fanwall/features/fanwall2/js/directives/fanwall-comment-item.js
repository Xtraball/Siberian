/**
 * fanwallCommentHistoryItem
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .directive('fanwallCommentItem', function ($rootScope, $filter, $timeout, $translate, $q, Customer, Dialog, Loader,
                                               Fanwall, FanwallPost, FanwallUtils, Popover) {
        return {
            restrict: 'E',
            templateUrl: 'features/fanwall2/assets/templates/l1/modal/directives/comment-item.html',
            controller: function ($scope) {
                $scope.actionsPopover = null;
                $scope.popoverItems = [];

                $scope.getCardDesign = function () {
                    return Fanwall.getSettings().cardDesign;
                };

                $scope.getSettings = function () {
                    return Fanwall.getSettings();
                };

                $scope.authorImagePath = function () {
                    if ($scope.comment.author &&
                        $scope.comment.author.image &&
                        $scope.comment.author.image.length > 0) {
                        return IMAGE_URL + 'images/customer' + $scope.comment.author.image;
                    }
                    return './features/fanwall2/assets/templates/images/customer-placeholder.png'
                };

                $scope.isBlocked = function () {
                    return $scope.comment.isBlocked;
                };

                $scope.isFromMe = function () {
                    return $scope.comment &&
                        ($scope.comment.customerId === Customer.customer.id);
                };

                $scope.imagePath = function () {
                    return IMAGE_URL + 'images/application' + $scope.comment.image;
                };

                $scope.showText = function () {
                    return $filter('linky')($scope.comment.text);
                };

                $scope.authorName = function () {
                    return $scope.comment.author.firstname + " " + $scope.comment.author.lastname;
                };

                $scope.publicationDate = function () {
                    return $filter('moment_calendar')($scope.comment.date * 1000);
                };

                $scope.isOwner = function () {
                    if (!Customer.isLoggedIn()) {
                        return false;
                    }

                    return Customer.customer.id === $scope.comment.customerId;
                };

                $scope.deleteComment = function () {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }

                    var title = $translate.instant("Delete this comment!", "fanwall");
                    var message = $translate.instant("Are you sure?", "fanwall");

                    return Dialog
                        .confirm(
                            title,
                            message,
                            ['YES', 'NO'])
                        .then(function (success) {
                            if (success) {
                                Loader.show();

                                FanwallPost
                                    .deleteComment($scope.comment.id)
                                    .then(function (payload) {
                                        $scope.post.comments = angular.copy(payload.comments);
                                        $scope.post.commentCount = $scope.post.comments.length;
                                    }, function (payload) {
                                        Dialog.alert('Error!', payload.message, 'OK', -1, 'fanwall');
                                    }).then(function () {
                                    Loader.hide();
                                });
                            }
                        });
                };

                $scope.flagComment = function () {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }

                    var title = $translate.instant('Report this message!', 'fanwall');
                    var message = $translate.instant('Please let us know why you think this message is inappropriate.', 'fanwall');
                    var placeholder = $translate.instant('Your message.', 'fanwall');

                    return Dialog
                        .prompt(
                            title,
                            message,
                            'text',
                            placeholder,
                            ['OK', 'CANCEL'],
                            -1,
                            'fanwall')
                        .then(function (value) {
                            Loader.show();

                            FanwallPost
                                .reportComment($scope.comment.id, value)
                                .then(function (payload) {
                                    Dialog.alert('Thanks!', payload.message, 'OK', 2350, 'fanwall');
                                }, function (payload) {
                                    Dialog.alert('Error!', payload.message, 'OK', -1, 'fanwall');
                                }).then(function () {
                                Loader.hide();
                            });
                        });
                };

                // Popover actions!
                $scope.openActions = function ($event) {
                    $scope
                        .closeActions()
                        .then(function () {
                            Popover
                                .fromTemplateUrl('features/fanwall2/assets/templates/l1/modal/directives/actions-popover.html', {
                                    scope: $scope
                                }).then(function (popover) {
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

                $scope.blockUser = function () {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }

                    FanwallUtils.blockUser($scope.comment.id, 'from-comment');
                };

                $scope.unblockUser = function () {
                    FanwallUtils.unblockUser($scope.comment.id, 'from-comment');
                };

                $scope.editComment = function () {
                    return FanwallUtils.editCommentModal($scope.comment);
                };

                $scope.showHistory = function () {
                    FanwallUtils.showCommentHistoryModal($scope.comment);
                };

                /**
                 *
                 */
                $scope.buildPopoverItems = function () {
                    $scope.popoverItems = [];

                    var historyAction = {
                        label: $translate.instant('View edit history', 'fanwall'),
                        icon: 'icon ion-clock',
                        click: function () {
                            $scope
                                .closeActions()
                                .then(function () {
                                    $scope.showHistory();
                                });
                        }
                    };

                    if ($scope.isOwner()) {
                        $scope.popoverItems.push({
                            label: $translate.instant('Edit comment', 'fanwall'),
                            icon: 'icon ion-edit',
                            click: function () {
                                $scope
                                    .closeActions()
                                    .then(function () {
                                        $scope.editComment();
                                    });
                            }
                        });

                        if ($scope.comment.history.length > 0) {
                            $scope.popoverItems.push(historyAction);
                        }

                        $scope.popoverItems.push({
                            label: $translate.instant('Delete comment', 'fanwall'),
                            icon: 'icon ion-android-delete',
                            click: function () {
                                $scope
                                    .closeActions()
                                    .then(function () {
                                        $scope.deleteComment();
                                    });
                            }
                        });
                    } else {
                        if (!$scope.isBlocked()) {
                            $scope.popoverItems.push({
                                label: $translate.instant('Report post', 'fanwall'),
                                icon: 'icon ion-flag',
                                click: function () {
                                    $scope
                                        .closeActions()
                                        .then(function () {
                                            $scope.flagComment();
                                        });
                                }
                            });

                            if ($scope.comment.history.length > 0) {
                                $scope.popoverItems.push(historyAction);
                            }

                            $scope.popoverItems.push({
                                label: $translate.instant('Block all user posts', 'fanwall'),
                                icon: 'ion-android-remove-circle',
                                click: function () {
                                    $scope
                                        .closeActions()
                                        .then(function () {
                                            $scope.blockUser();
                                        });
                                }
                            });
                        } else {
                            $scope.popoverItems.push({
                                label: $translate.instant('Unblock user', 'fanwall'),
                                icon: 'ion-android-remove-circle',
                                click: function () {
                                    $scope
                                        .closeActions()
                                        .then(function () {
                                            $scope.unblockUser();
                                        });
                                }
                            });
                        }
                    }
                };

                // Build items!
                $scope.buildPopoverItems();

                $rootScope.$on('fanwall.refresh.comments', function (event, payload) {
                    // Comments are updated!
                    if (payload.postId === $scope.post.id) {
                        $timeout(function () {
                            $scope.buildPopoverItems();
                        });
                    }
                });
            }
        };
    });


