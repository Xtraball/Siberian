/**
 * fanwallPostItem
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .directive('fanwallPostItem', function ($rootScope, $filter, $sce, $translate, $timeout, $q,
                                            Application, Customer, Dialog, Loader, Fanwall, FanwallPost, FanwallUtils,
                                            Lightbox, Popover, SocialSharing) {
        return {
            restrict: 'E',
            templateUrl: 'features/fanwall2/assets/templates/l1/tabs/directives/post-item.html',
            controller: function ($scope) {
                $scope.actionsPopover = null;
                $scope.popoverItems = [];
                $scope.post.isFull = false;

                $scope.getCardDesign = function () {
                    return Fanwall.getSettings().cardDesign;
                };

                $scope.getSettings = function () {
                    return Fanwall.getSettings();
                };

                $scope.userLike = function () {
                    return $scope.getSettings().features.enableUserLike;
                };

                $scope.userComment = function () {
                    return $scope.getSettings().features.enableUserComment;
                };

                $scope.userShareBig = function () {
                    return ['big', 'both'].indexOf($scope.getSettings().features.enableUserShare) >= 0;
                };

                $scope.userShareSmall = function () {
                    return ['small', 'both'].indexOf($scope.getSettings().features.enableUserShare) >= 0;
                };

                $scope.getColSizeTextual = function () {
                    var counter = 0;
                    if ($scope.userLike()) {
                        counter++;
                    }
                    if ($scope.userComment()) {
                        counter++;
                    }

                    switch (counter) {
                        default:
                        case 0:
                        case 1:
                            return 'col-100';
                        case 2:
                            return 'col-50';
                    }
                };

                $scope.getColSize = function () {
                    var counter = 0;
                    if ($scope.userLike()) {
                        counter++;
                    }
                    if ($scope.userComment()) {
                        counter++;
                    }
                    if ($scope.userShareBig()) {
                        counter++;
                    }

                    switch (counter) {
                        default:
                        case 0:
                        case 1:
                            return 'col-100';
                        case 2:
                            return 'col-50';
                        case 3:
                            return 'col-33';
                    }
                };

                $scope.photoMode = function () {
                    return $scope.getSettings().photoMode;
                };

                $scope.photoPosition = function () {
                    return $scope.getSettings().photoPosition;
                };

                $scope.showText = function () {
                    return $filter('linky')($scope.post.text);
                };

                $scope.showLikeOrComment = function () {
                    return ($scope.post.likeCount > 0 || $scope.post.commentCount > 0) &&
                        ($scope.canLikeOrComment());
                };

                $scope.canLikeOrComment = function () {
                    return ($scope.userLike() || $scope.userComment());
                };

                $scope.getMaxBodySize = function () {
                    return $scope.getSettings().maxBodySize;
                };

                $scope.textIsCut = function () {
                    var maxLength = $scope.getMaxBodySize();
                    return maxLength > 0 && $scope.post.text.length > maxLength
                };

                $scope.cutBody = function () {
                    var maxLength = $scope.getMaxBodySize();
                    if (maxLength <= 0 || $scope.post.text.length <= maxLength) {
                        return $scope.post.text;
                    }
                    return $scope.post.text.substring(0, maxLength) + '...';
                };

                $scope.imagePath = function (path) {
                    if (path !== undefined) {
                        return IMAGE_URL + 'images/application' + path;
                    }
                    if ($scope.post.image.length <= 0) {
                        return './features/fanwall2/assets/templates/images/placeholder.png';
                    }
                    return IMAGE_URL + 'images/application' + $scope.post.image;
                };

                $scope.authorImagePath = function () {
                    // Empty image
                    if ($scope.post.author.image.length <= 0) {
                        return './features/fanwall2/assets/templates/images/customer-placeholder.png';
                    }
                    // App icon
                    if ($scope.post.author.image.indexOf('/var/cache') === 0) {
                        return IMAGE_URL + $scope.post.author.image;
                    }
                    return IMAGE_URL + 'images/customer' + $scope.post.author.image;
                };

                $scope.liked = function () {
                    return $scope.post.likes;
                };

                $scope.authorName = function () {
                    return $scope.post.author.firstname + ' ' + $scope.post.author.lastname;
                };

                $scope.publicationDate = function () {
                    return $filter('moment_calendar')($scope.post.date * 1000);
                };

                // Popover actions!
                $scope.openActions = function ($event) {
                    $scope
                        .closeActions()
                        .then(function () {
                            Popover
                                .fromTemplateUrl('features/fanwall2/assets/templates/l1/tabs/directives/actions-popover.html', {
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

                $scope.sharePost = function () {
                    var shareLink = [
                        'https://',
                        Application.application.share_domain,
                        '/',
                        APP_KEY,
                        '/fanwall/post/',
                        Fanwall.lastValueId,
                        '/',
                        $scope.post.id
                    ].join('');
                    SocialSharing.share(
                        '',
                        $translate.instant('Check this post!', 'fanwall'),
                        '',
                        shareLink);
                };

                $scope.flagPost = function () {
                    var title = $translate.instant('Report this message!', 'fanwall');
                    var message = $translate.instant('Please let us know why you think this message is inappropriate.', 'fanwall');
                    var placeholder = $translate.instant('Your message.', 'fanwall');

                    Dialog
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
                                .reportPost($scope.post.id, value)
                                .then(function (payload) {
                                    Dialog.alert('Thanks!', payload.message, 'OK', 2350, 'fanwall');
                                }, function (payload) {
                                    Dialog.alert('Error!', payload.message, 'OK', -1, 'fanwall');
                                }).then(function () {
                                Loader.hide();
                            });
                        });
                };

                /**
                 *
                 * */
                $scope.buildPopoverItems = function () {
                    var viewHistory = {
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

                    if ($scope.userShareSmall()) {
                        $scope.popoverItems.push({
                            label: $translate.instant('Share', 'fanwall'),
                            icon: 'icon ion-sb-share-filled',
                            click: function () {
                                $scope
                                    .closeActions()
                                    .then(function () {
                                        $scope.sharePost();
                                    });
                            }
                        });
                    }

                    if ($scope.isOwner()) {
                        $scope.popoverItems.push({
                            label: $translate.instant('Edit post', 'fanwall'),
                            icon: 'icon ion-edit',
                            click: function () {
                                $scope
                                    .closeActions()
                                    .then(function () {
                                        $scope.editPost();
                                    });
                            }
                        });

                        if ($scope.post.history.length > 0) {
                            $scope.popoverItems.push(viewHistory);
                        }

                        var deleteText = $translate.instant('Trash post', 'fanwall');
                        if ($scope.post.status === 'deleted') {
                            deleteText = $translate.instant('Delete permanently', 'fanwall');
                        }

                        $scope.popoverItems.push({
                            label: deleteText,
                            icon: 'icon ion-android-delete',
                            click: function () {
                                $scope
                                    .closeActions()
                                    .then(function () {
                                        $scope.deletePost();
                                    });
                            }
                        });
                    } else {
                        $scope.popoverItems.push({
                            label: $translate.instant('Report post', 'fanwall'),
                            icon: 'icon ion-flag',
                            click: function () {
                                $scope
                                    .closeActions()
                                    .then(function () {
                                        $scope.flagPost();
                                    });
                            }
                        });

                        if ($scope.post.history.length > 0) {
                            $scope.popoverItems.push(viewHistory);
                        }

                        if ($scope.post.customerId !== 0) {
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
                        }

                    }
                };

                $scope.blockUser = function () {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }
                    FanwallUtils.blockUser($scope.post.id, 'from-post');
                };

                $scope.showHistory = function () {
                    FanwallUtils.showPostHistoryModal($scope.post);
                };

                $scope.deletePost = function () {
                    Dialog
                        .confirm(
                            'Confirmation',
                            'You are about to delete this post!',
                            ['YES', 'NO'],
                            -1,
                            'fanwall')
                        .then(function (value) {
                            if (!value) {
                                return;
                            }
                            Loader.show();

                            FanwallPost
                                .deletePost($scope.post.id, value)
                                .then(function (payload) {
                                    $rootScope.$broadcast('fanwall.refresh');
                                }, function (payload) {
                                    Dialog.alert('Error!', payload.message, 'OK', -1, 'fanwall');
                                }).then(function () {
                                Loader.hide();
                            });
                        });
                };

                $scope.commentModal = function () {
                    // Opens comment modal only if it's enabled!
                    if ($scope.userComment()) {
                        FanwallUtils.commentModal($scope.post);
                    }
                };

                $scope.toggleLike = function () {
                    if (!Customer.isLoggedIn()) {
                        return Customer.loginModal();
                    }

                    // Prevent spamming like/unlike!
                    if ($scope.post.likeLocked === true) {
                        return false;
                    }

                    $scope.post.likeLocked = true;
                    if ($scope.post.iLiked) {
                        // Instant feedback while saving value!
                        $scope.post.iLiked = false;

                        FanwallPost
                            .unlike($scope.post.id)
                            .then(function (payload) {
                                // Decrease like count if success!
                                $scope.post.likeCount--;
                            }, function (payload) {
                                // Revert value if failed!
                                $scope.post.iLiked = true;
                            }).then(function () {
                            $scope.post.likeLocked = false;
                        });

                    } else {
                        // Instant feedback while saving value!
                        $scope.post.iLiked = true;

                        FanwallPost
                            .like($scope.post.id)
                            .then(function (payload) {
                                // Increase like count if success!
                                $scope.post.likeCount++;
                            }, function (payload) {
                                // Revert value if failed!
                                $scope.post.iLiked = false;
                            }).then(function () {
                            $scope.post.likeLocked = false;
                        });
                    }

                    return true;
                };

                $scope.isOwner = function () {
                    if (!Customer.isLoggedIn()) {
                        return false;
                    }

                    return Customer.customer.id === $scope.post.customerId;
                };

                $scope.editPost = function () {
                    return FanwallUtils.postModal($scope.post);
                };

                // Build items!
                $scope.buildPopoverItems();

                $rootScope.$on('fanwall.modal.ready', function () {
                    $timeout(function () {
                        Lightbox.run('.show-post');
                    }, 200);
                });
            }
        };
    });


