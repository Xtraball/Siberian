/* global
 App, angular
 */

/**
 * Newswall
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Newswall', function ($pwaRequest) {
    var factory = {
        value_id: null,
        displayed_per_page: 0,
        extendedOptions: {},
        collection: []
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function (value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     */
    factory.preFetch = function () {
        factory.findAll();
    };

    factory.findAll = function (offset, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.findAll] missing value_id');
        }

        return $pwaRequest.get('comment/mobile_list/findall', angular.extend({
            urlParams: {
                value_id: this.value_id,
                offset: offset
            },
            refresh: refresh
        }, factory.extendedOptions));
    };

    factory.findNear = function (offset, position) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.findNear] missing value_id');
        }

        return $pwaRequest.get('comment/mobile_list/findnear', {
            urlParams: {
                value_id: this.value_id,
                offset: offset,
                latitude: position.latitude,
                longitude: position.longitude
            },
            cache: false
        });
    };

    factory.findAllPhotos = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.findAllPhotos] missing value_id');
        }

        return $pwaRequest.get('comment/mobile_gallery/findall', {
            urlParams: {
                value_id: this.value_id
            }
        });
    };

    factory.findAllLocation = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.findAllLocation] missing value_id');
        }

        return $pwaRequest.get('comment/mobile_map/findall', {
            urlParams: {
                value_id: this.value_id
            }
        });
    };

    factory.find = function (comment_id) {
        if (!this.value_id || (comment_id === undefined)) {
            return $pwaRequest.reject('[Factory::Newswall.find] missing value_id or comment_id');
        }

        return $pwaRequest.get('comment/mobile_view/find', {
            urlParams: {
                value_id: this.value_id,
                comment_id: comment_id
            }
        });
    };

    /**
     * Search for comment payload inside cached collection
     *
     * @param comment_id
     * @returns {*}
     */
    factory.getComment = function (comment_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.getComment] missing value_id');
        }

        var comment = _.get(_.filter(factory.collection, function (comment) {
            return (comment.id == comment_id);
        })[0], 'embed_payload', false);

        if (!comment) {
            /** Well then fetch it. */
            return factory.find(comment_id);
        }

            return $pwaRequest.resolve(comment);
    };

    /**
     *
     * @param comment_id
     * @param answer
     */
    factory.insertAnswer = function (comment_id, answer) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.insertComment] missing value_id');
        }

        _.forEach(factory.collection, function (comment) {
            if (comment.id == comment_id) {
                if (comment.hasOwnProperty('embed_payload')) {
                    if (comment.embed_payload.hasOwnProperty('answers')) {
                        comment.embed_payload.answers.push(answer);
                    } else {
                        comment.embed_payload.answers = [answer];
                    }
                    /** Also updates the number of comments. */
                    comment.details.comments.text = comment.embed_payload.answers.length;
                }
            }
        });
    };

    factory.addLike = function (comment_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.addLike] missing value_id');
        }

        return $pwaRequest.post('comment/mobile_view/addlike', {
            data: {
                comment_id: comment_id,
                value_id: this.value_id
            },
            cache: false
        }).then(function (data) {
            /** Trigger a cache refresh. */
            $pwaRequest.get('comment/mobile_list/findall', {
                urlParams: {
                    value_id: this.value_id
                },
                refresh: true
            });

            return data;
        });
    };

    factory.flagPost = function (comment_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.flagPost] missing value_id');
        }

        return $pwaRequest.get('comment/mobile_view/flagpost', {
            urlParams: {
                value_id: this.value_id,
                comment_id: comment_id
            },
            cache: false
        });
    };

    factory.flagComment = function (answer_id) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.flagComment] missing value_id');
        }

        return $pwaRequest.get('comment/mobile_view/flagcomment', {
            urlParams: {
                value_id: this.value_id,
                answer_id: answer_id
            },
            cache: false
        });
    };

    factory.createComment = function (text, image, position) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::Newswall.createComment] missing value_id');
        }

        var params = {};
        if (position && position.latitude && position.longitude) {
            params.position = {
                latitude: position.latitude,
                longitude: position.longitude
            };
        }

        return $pwaRequest.post('comment/mobile_edit/createv2', {
            urlParams: {
                value_id: this.value_id
            },
            data: angular.extend(params, {
                value_id: this.value_id,
                text: text,
                image: image
            }),
            cache: false
        });
    };

    return factory;
});

/**
 * Comment
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Comment', function ($pwaRequest) {
    var factory = {
        extendedOptions: {},
        collection: []
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    factory.findAll = function (comment_id) {
        if (!comment_id) {
            return $pwaRequest.reject('[Factory::Comment.findAll] missing comment_id');
        }

        return $pwaRequest.get('comment/mobile_comment/findall', {
            urlParams: {
                comment_id: comment_id
            }
        });
    };

    factory.add = function (comment) {
        if (!comment.id) {
            return $pwaRequest.reject('[Factory::Comment.add] missing comment_id');
        }

        return $pwaRequest.post('comment/mobile_comment/add', {
            data: {
                comment_id: comment.id,
                text: comment.text
            },
            cache: false
        }).then(function (data) {
            // Trigger a cache refresh!
            $pwaRequest.get('comment/mobile_comment/findall', {
                urlParams: {
                    comment_id: comment.id
                },
                refresh: true
            });

            return data;
        });
    };

    return factory;
});
