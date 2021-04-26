/**
 * Module FanWall
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.18.20
 */
angular
    .module('starter')
    .factory('FanwallPost', function ($pwaRequest, $stateParams) {

        var factory = {
            storage: []
        };

        factory.findAll = function (offset, refresh) {
            var storageKey = [$stateParams.value_id, 'findAll', offset].join('_');
            if (refresh !== true &&
                factory.storage.hasOwnProperty(storageKey)) {
                return $pwaRequest.resolve(factory.storage[storageKey]);
            }

            var promise = $pwaRequest.get('fanwall/mobile_post/find-all', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id,
                    offset: offset
                },
                refresh: refresh
            }, factory.extendedOptions));

            promise.then(function (success) {
                factory.storage[storageKey] = success;
            });

            return promise;
        };

        factory.findOne = function (postId) {
            return $pwaRequest.get('fanwall/mobile_post/find-one', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id,
                    postId: postId
                },
                refresh: true
            }, factory.extendedOptions));
        };

        factory.findAllNearby = function (location, offset, refresh) {
            var storageKey = [$stateParams.value_id, 'findAllNearby', location.latitude, location.longitude, offset].join('_');
            if (refresh !== true &&
                factory.storage.hasOwnProperty(storageKey)) {
                return $pwaRequest.resolve(factory.storage[storageKey]);
            }

            var promise = $pwaRequest.get('fanwall/mobile_post/find-all-nearby', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id,
                    latitude: location.latitude,
                    longitude: location.longitude,
                    offset: offset
                },
                refresh: refresh
            }, factory.extendedOptions));

            promise.then(function (success) {
                factory.storage[storageKey] = success;
            });

            return promise;
        };

        factory.findAllProfile = function (offset) {
            return $pwaRequest.get('fanwall/mobile_post/find-all-profile', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id,
                    offset: offset
                },
                refresh: true
            }, factory.extendedOptions));
        };

        factory.findAllBlocked = function () {
            return $pwaRequest.get('fanwall/mobile_post/find-all-blocked', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id
                },
                refresh: true
            }, factory.extendedOptions));
        };

        factory.findAllMap = function (location, offset, refresh) {
            var storageKey = [$stateParams.value_id, 'findAllMap', location.latitude, location.longitude, offset].join('_');
            if (refresh !== true &&
                factory.storage.hasOwnProperty(storageKey)) {
                return $pwaRequest.resolve(factory.storage[storageKey]);
            }

            var promise = $pwaRequest.get('fanwall/mobile_post/find-all-map', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id,
                    latitude: location.latitude,
                    longitude: location.longitude,
                    offset: offset
                },
                refresh: refresh
            }, factory.extendedOptions));

            promise.then(function (success) {
                factory.storage[storageKey] = success;
            });

            return promise;
        };

        factory.like = function (postId) {
            return $pwaRequest.post('fanwall/mobile_post/like-post', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id,
                    postId: postId
                }
            }, factory.extendedOptions));
        };

        factory.unlike = function (postId) {
            return $pwaRequest.post('fanwall/mobile_post/unlike-post', angular.extend({
                urlParams: {
                    value_id: $stateParams.value_id,
                    postId: postId
                }
            }, factory.extendedOptions));
        };

        /**
         * Send new post!
         *
         * @param postId
         * @param form
         */
        factory.sendPost = function (postId, form) {
            return $pwaRequest.post('fanwall/mobile_post/send-post', {
                urlParams: {
                    value_id: $stateParams.value_id
                },
                data: {
                    postId: postId,
                    form: form
                },
                cache: false
            });
        };

        /**
         * Send new comment!
         *
         * @param postId
         * @param commentId
         * @param form
         */
        factory.sendComment = function (postId, commentId, form) {
            return $pwaRequest.post('fanwall/mobile_post/send-comment', {
                urlParams: {
                    value_id: $stateParams.value_id
                },
                data: {
                    postId: postId,
                    commentId: commentId,
                    form: form
                },
                cache: false
            });
        };

        /**
         * Report unwanted post!
         *
         * @param postId
         * @param reportMessage
         */
        factory.reportPost = function (postId, reportMessage) {
            return $pwaRequest.post('fanwall/mobile_report/report-post', {
                urlParams: {
                    value_id: $stateParams.value_id
                },
                data: {
                    postId: postId,
                    reportMessage: reportMessage
                }
            });
        };

        /**
         * Block unwanted user!
         *
         * @param sourceId
         * @param from
         */
        factory.blockUser = function (sourceId, from) {
            return $pwaRequest.post('fanwall/mobile_post/block-user', {
                urlParams: {
                    value_id: $stateParams.value_id
                },
                data: {
                    from: from,
                    sourceId: sourceId
                }
            });
        };

        /**
         * Block unwanted user!
         *
         * @param sourceId
         * @param from
         */
        factory.unblockUser = function (sourceId, from) {
            return $pwaRequest.post('fanwall/mobile_post/unblock-user', {
                urlParams: {
                    value_id: $stateParams.value_id
                },
                data: {
                    from: from,
                    sourceId: sourceId
                }
            });
        };

        /**
         * Delete own post!
         *
         * @param postId
         */
        factory.deletePost = function (postId) {
            return $pwaRequest.post('fanwall/mobile_post/delete-post', {
                urlParams: {
                    value_id: $stateParams.value_id
                },
                data: {
                    postId: postId
                }
            });
        };

        /**
         * Delete self comment!
         *
         * @param commentId
         */
        factory.deleteComment = function (commentId) {
            return $pwaRequest.post('fanwall/mobile_post/delete-comment', {
                urlParams: {
                    value_id: $stateParams.value_id,
                    commentId: commentId
                }
            });
        };

        /**
         * Report unwanted comment!
         *
         * @param commentId
         * @param reportMessage
         */
        factory.reportComment = function (commentId, reportMessage) {
            return $pwaRequest.post('fanwall/mobile_report/report-comment', {
                urlParams: {
                    value_id: $stateParams.value_id
                },
                data: {
                    commentId: commentId,
                    reportMessage: reportMessage
                }
            });
        };

        return factory;
    });
