/*global
    App, angular
 */
angular.module("starter").config(['$provide', function($provide) {

    // Based on http://www.bennadel.com/blog/2615-posting-form-data-with-http-in-angularjs.htm
    // But we a really really more efficient version of serializeData
    var transformAsFormPost = function( data, getHeaders ) {
        function serializeData (data, prefix) {
            if(!angular.isString(prefix)) {
                prefix = "";
            }

            prefix = prefix.trim();

            if(angular.isArray(data) && (prefix.length < 1)) {
                prefix = "data";
            }


            var pairs = [];
            if(angular.isObject(data) || angular.isArray(data)) {
                var index = 0;
                angular.forEach(data, function (value, key) {
                    if(angular.isArray(data)) {
                        key = index;
                    }

                    key = encodeURIComponent(key);

                    if (prefix.length > 0) {
                        key = prefix + "[" + key + "]";
                    }

                    if (angular.isArray(value) || angular.isObject(value)) {
                        pairs.push(serializeData(value, key));
                    } else {
                        if (angular.isUndefined(value) || value === null) {
                            value = "";
                        }

                        pairs.push(key + "=" + encodeURIComponent(value));
                    }
                    index += 1;
                });
            }

            return pairs.join( "&" ).replace( /%20/g, "+" );
        }

        return( serializeData( data ) );
    };

    $provide.decorator('$http', [
        '$delegate', function($delegate) {

            $delegate.postForm = function(url, data, config) {
                if(!angular.isObject(config)) {
                    config = {};
                }

                if(!angular.isObject(config.headers)) {
                    config.headers = {};
                }

                config.headers["Content-Type"] = "application/x-www-form-urlencoded; charset=utf-8";

                if(angular.isArray(config.transformRequest)) {
                    config.transformRequest.push(transformAsFormPost);
                }
                else if(angular.isFunction(config.transformRequest)) {
                    config.transformRequest = [config.transformRequest, transformAsFormPost];
                }
                else {
                    config.transformRequest = transformAsFormPost;
                }

                return $delegate.post(url, data, config);
            };

            return $delegate;
        }
    ]);

}]);
