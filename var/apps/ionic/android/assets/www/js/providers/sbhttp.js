
App.provider('$sbhttp', function httpCacheLayerProvider() {
    var provider = {
        alwaysCache: false,
        neverCache: false,
        debug: false
    };

    provider.$get = [
        "$rootScope", "$http", "$log", "$q", "$window", "_",
        function httpCacheLayerFactory($rootScope, $http, $log, $q, $window, _) {
            var httpCacheLayerConfig = {
                alwaysCache: provider.alwaysCache,
                neverCache: (!provider.alwaysCache && provider.neverCache),
                debug: provider.debug === true
            };

            var httpCache;
            httpCache = {};
            httpCache.getItem = httpCache.setItem = httpCache.removeItem = function() {
                return $q.reject("no offline mode cache in webview");
            };

            if((ionic.Platform.isIOS() || ionic.Platform.isAndroid()) && window.localforage) {
                window.localforage.config({
                    name        : 'sb-offline-mode',
                    storeName   : 'keyvaluepairs',
                    size        : 262144000
                });
                httpCache = window.localforage;
            }

            function httpCacheLayer(httpCacheLayerConfig) {

                var httpWrapper = function(requestOptions) {
                    var method = _.upperCase(_.trim(_.get(requestOptions, "method")));
                    var url = _.trim(_.get(requestOptions, "url"));

                    $log.debug((new Error("Stacktrace following")).stack);

                    if(method === "GET" && url.length > 0) {
                        $log.debug("GET "+url);
                        var isOnline = $rootScope.isOnline;
                        var cache = (!httpCacheLayerConfig.neverCache || httpCacheLayerConfig.alwaysCache);
                        var cacheRequest = _.get(requestOptions, "cache", cache);

                        var success_cbs = [angular.noop];
                        var error_cbs = [angular.noop];

                        var request_done = false;
                        var request_error = false;

                        var promise = $q(function(resolve, reject) {
                            var process = function(cachedResponse) {
                                try {
                                    // Not sure why, cached response is sometimes double json stringified
                                    while(_.isString(cachedResponse) && _.trim(cachedResponse).length > 0) {
                                        cachedResponse = JSON.parse(cachedResponse);
                                    }
                                } catch(e) {
                                    $log.info("Error parsing data :", e, data);
                                    cachedResponse = null;
                                }

                                var response = cacheRequest && cachedResponse;

                                var config = _.extend({}, requestOptions, {
                                });

                                var process_response = function(http_response) {
                                    $log.debug("Processing http response ("+url+") with status code "+_.get(http_response, "status"));

                                    if(_.isObject(http_response)
                                        && (http_response.status === 0)
                                        && _.isObject(response)) { // If we've been disconnected during the request

                                        isOnline = false;
                                        $log.debug("request failed for "+url+": using cache");
                                        return process_response(response);
                                    }

                                    response = http_response;

                                    request_done = true;
                                    request_error = !((response.status >= 200) && (response.status <= 299));

                                    var callbacks = request_error ? error_cbs : success_cbs;
                                    var promise_resolver = request_error ? reject : resolve;

                                    var sendResult = function() {
                                        if(_.isFunction(response.headers) && (response.headers("X-From-Native-Cache") === "true")) {
                                            response = _.extend({}, response, {fromCache: true});
                                        }

                                        _.forEach(callbacks, function(cb) {
                                            cb(response.data, response.status, response.headers, config);
                                        });
                                        promise_resolver(response);
                                    };

                                    if(_.isObject(response) && (isOnline || (response.fromCache !== true)) && cacheRequest && !request_error) {
                                        $log.debug("caching response for URL "+url+" and status "+_.get(http_response, "status"));

                                        var data = JSON.stringify(_.extend({}, response, {fromCache: true}));

                                        try {
                                            data = JSON.stringify(data);
                                        } catch(e) {
                                            $log.info("Error stringifying data :", e, data);
                                        }

                                        return httpCache.setItem(url, data).then(
                                            sendResult,
                                            function(err) {
                                                $log.debug("LOCAL FORAGE ERROR : ", err);
                                                sendResult();
                                            });
                                    }

                                    return sendResult();
                                };

                                if(_.isObject(response) && !isOnline) {
                                    $log.debug("we're offline: using cache");
                                    process_response(response);
                                } else {
                                    $log.debug("sending http call with config: ", config);
                                    $http(config).then(
                                        process_response,
                                        process_response
                                    );
                                }
                            };

                            httpCache.getItem(url).then(process, function(err) {
                                $log.debug("Error retrieving data from cache data :", err);
                                return process(null);
                            });

                        });

                        promise.success = function(callback) {
                            if(_.isFunction(callback)) {
                                success_cbs.push(callback);
                            }

                            if(request_done && !request_error) {
                                callback(response.data, response.status, response.headers, config);
                            }

                            return promise;
                        };

                        promise.error = function(callback) {
                            if(_.isFunction(callback)) {
                                error_cbs.push(callback);
                            }

                            if(request_done && request_error) {
                                callback(response.data, response.status, response.headers, config);
                            }

                            return promise;
                        };

                        return promise;
                    }

                    return $http(requestOptions);
                };

                httpWrapper.get = function(url, config) {
                    return  httpWrapper(_.extend({}, config || {}, {
                        method: "GET",
                        url: url
                    }));
                };

                httpWrapper.head    = $http.head;
                httpWrapper.post    = $http.post;
                httpWrapper.put     = $http.put;
                httpWrapper.delete  = $http.delete;
                httpWrapper.jsonp   = $http.jsonp;
                httpWrapper.patch   = $http.patch;

                // This needs to be dynamic because postForm is an angular decorator
                Object.defineProperty(httpWrapper, "postForm", {
                    get: function() {
                        return $http.postForm;
                    }
                });

                httpWrapper.cache = function(uri) {
                    if(!$rootScope.is_webview && window.OfflineMode) {
                        return $q(function(resolve, reject) { window.OfflineMode.cacheURL(uri, function() {
                            $log.info("cached URL succesfully : ", uri);
                            resolve();
                        }, function() {
                            $log.info("Failed to cache URL : ", uri);
                            reject();
                        }); });
                    }

                    return $q.reject();
                };

                httpWrapper.removeCached = function(uri) {
                    return httpCache.removeItem(uri);
                };

                return httpWrapper;
            }

            return new httpCacheLayer(httpCacheLayerConfig);
        }
    ];

    return provider;
});
