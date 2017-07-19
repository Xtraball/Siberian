/**
 * Angular Queue
 * @version v0.0.1
 * @author James Seppi
 * @license MIT License, http://jseppi.mit-license.org
 */
(function(window, angular, undefined) {
    'use strict';

    angular.module('ngQueue', []).factory('$queue',
        ['$timeout', '$q',
            function($timeout, $q) {

                var defaults = {
                    delay           : 100,
                    persistent      : false,
                    max_concurrent  : -1,
                    complete        : null,
                    paused          : false
                };

                /**
                 * Implementation of the Queue class
                 *
                 */
                function Queue(callback, options) {
                    options = angular.extend({}, defaults, options);

                    if (!angular.isFunction(callback)) {
                        throw new Error("callback must be a function");
                    }

                    //-- Private variables
                    var cleared = false,
                        timeoutProm = null;

                    //-- Public variables
                    this.queue              = [];
                    this.delay              = options.delay;
                    this.complete           = options.complete;
                    this.paused             = options.paused;
                    this.max_concurrent     = options.max_concurrent;
                    this.persistent         = options.persistent;
                    this.user_callback      = callback;
                    this.active_count       = 0;
                    this.global_pause        = false;

                    //-- Private methods
                    /**
                     * stop() stops processing of the queue
                     *
                     */
                    var stop = function() {
                        if (timeoutProm) {
                            $timeout.cancel(timeoutProm);
                        }

                        timeoutProm = null;
                    };


                    //-- Privileged/Public methods

                    /**
                     * Callback wrapper with a promise for concurrent threads
                     *
                     * @param item
                     * @returns {*}
                     */
                    this.callback = function(item) {
                        var _this = this;

                        console.info("remain in queue: ", this.size());

                        $timeout(function() {
                            _this.user_callback.call(_this, item);
                        }, 1);

                        if(item.network_promise !== undefined) {
                            return item.network_promise.promise;
                        } else {
                            return $q.resolve();
                        }
                    };

                    /**
                     * size() returns the size of the queue
                     *
                     * @return<Number> queue size
                     */
                    this.size = function() {
                        return this.queue.length;
                    };

                    /**
                     * add() adds an item to the back of the queue
                     *
                     * @param<Object> item
                     * @return<Number> queue size
                     */
                    this.add = function(item) {
                        console.log("Add item to queue: ", item);

                        return this.addEach([item]);
                    };

                    /**
                     * addFirst() adds an item to the top of the queue
                     *
                     * @param<Object> item
                     * @return<Number> queue size
                     */
                    this.addFirst = function(item) {
                        return this.addEachFirst([item]);
                    };

                    /**
                     * addEach() adds an array of items to the back of the queue
                     *
                     * @param<Array> items
                     * @return<Number> queue size
                     */
                    this.addEach = function(items) {
                        if (items) {
                            cleared = false;
                            this.queue = this.queue.concat(items);
                            window.ku = this.queue;
                        }

                        if (!this.paused) {
                            this.start();
                        }

                        return this.size();
                    };

                    /**
                     * addEach() adds an array of items to the back of the queue
                     *
                     * @param<Array> items
                     * @return<Number> queue size
                     */
                    this.addEachFirst = function(items) {
                        if (items) {
                            cleared = false;
                            this.queue = items.concat(this.queue);
                        }

                        if (!this.paused) {
                            this.start();
                        }

                        return this.size();
                    };

                    /**
                     * clear() clears all items from the queue
                     * and stops processing
                     *
                     * @return<Array> the original queue
                     */
                    this.clear = function() {
                        var orig = this.queue;
                        stop();
                        this.queue = [];
                        cleared = true;
                        return orig;
                    };

                    /**
                     * pause() pauses processing of the queue
                     *
                     */
                    this.pause = function() {
                        console.info("queue pause");
                        stop();
                        this.paused = true;
                    };

                    /**
                     * pause() pauses processing of the queue
                     *
                     */
                    this.globalPause = function() {
                        this.global_pause = true;
                        this.pause();
                    };

                    /**
                     *
                     */
                    this.globalStart = function() {
                        this.global_pause = false;
                        this.start();
                    };


                    /**
                     * start() starts processing of the queue.
                     * start() may be called after pause()
                     *
                     */
                    this.start = function() {
                        if(this.global_pause) {
                            return;
                        }

                        var _this = this;
                        this.paused = false;
                        if (this.size() && !timeoutProm) {
                            (function loopy() {
                                var item;

                                stop();

                                if(_this.paused) {
                                    return;
                                }

                                if((_this.max_concurrent > 0) && (_this.active_count > _this.max_concurrent)) {
                                    return;
                                }

                                /** If the queue is not persistent, call the complete callback on complete. */
                                if (!_this.size() && !_this.persistent) {
                                    cleared = true;
                                    if (angular.isFunction(_this.complete)) {
                                        _this.complete.call(_this);
                                    }
                                    return;
                                }

                                /** Clear when persistent */
                                if(!_this.size() && _this.persistent) {
                                    cleared = true;
                                    return;
                                }

                                /** Increase active count (only if nothing returned before) */
                                _this.active_count += 1;

                                item = _this.queue.shift();
                                var promise = _this.callback.call(_this, item);

                                /** No max concurrent threads, call after delay */
                                if(_this.max_concurrent === -1) {

                                    timeoutProm = $timeout(loopy, _this.delay);

                                } else {
                                    /** Call loopy only when callback is done */

                                    var callnext = function() {
                                        _this.active_count -= 1;

                                        timeoutProm = $timeout(loopy, _this.delay);
                                    };

                                    try {
                                        promise.then(function(success) {
                                            callnext();
                                        }, function(error) {
                                            callnext();
                                        }).catch(function(error) {
                                            callnext();
                                        });
                                    } catch(error) {
                                        callnext();
                                    }

                                }

                                return;
                            })();
                        }
                    };

                    /**
                     * indexOf() returns the first index of the item in the queue
                     *
                     * @param<Object> item
                     * @return<Number> index of the item if found, or -1 if not
                     */
                    this.indexOf = function(item) {
                        if (this.queue.indexOf) {
                            return this.queue.indexOf(item);
                        }

                        for (var i = 0; i < this.queue.length; i++) {
                            if (item === this.queue[i]) {
                                return i;
                            }
                        }
                        return -1;
                    };
                }

                /**
                 * queue() is a convenience function to return a new Queue
                 *
                 * Usage: var queue = $queue.queue(someOptions);
                 *
                 * @param<Function> callback - *Required* - Function called for
                 *          each item in the queue after each delay. Passed
                 *          item and called with the context of Queue.
                 * @param<Object> options
                 *      delay<Number> - *Optional* - Number of milliseconds between each
                 *          processing step of the queue. Defaults to 100.
                 *      complete<Function> - *Optional* - Function called upon
                 *          completion of processing all items in the queue.
                 *      paused<Boolean> - *Optional* - Flag to indicate if the queue
                 *          starts paused. If false, queue will start processing
                 *          items immediately after the first add or addEach.
                 * @return<Queue> a new Queue
                 */
                Queue.queue = function(callback, options) {
                    return new Queue(callback, options);
                };

                return Queue;
            }]);

})(window, window.angular);