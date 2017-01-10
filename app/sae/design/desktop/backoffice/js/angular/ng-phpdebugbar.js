(function() {
    'use strict';
    var handle_phpdebugbar_response = function(response) {

        if ((typeof phpdebugbar != "undefined") && (typeof phpdebugbar.ajaxHandler != "undefined")) {
            // We have a debugbar and an ajaxHandler
            // Dig through response to look for the
            // debugbar id.
            var headers = response && response.headers && response.headers();
            if (!headers) {
                return;
            }
            // Not very elegant, but this is how the debugbar.js defines the header.
            var headerName = phpdebugbar.ajaxHandler.headerName + '-id';
            var debugBarID = headers[headerName];
            if (debugBarID) {
                // A debugBarID was found! Now we just pass the
                // id to the debugbar to load the data
                phpdebugbar.loadDataSet(debugBarID, ('ajax'));
            }
        }
    };
    angular.module('ng-phpdebugbar', [])
        .factory('phpDebugBarInterceptor', ['$q', function($q) {
            return {
                'response': function(response) {
                    handle_phpdebugbar_response(response);
                    return response;
                },
                'responseError': function(rejection) {
                    handle_phpdebugbar_response(rejection);
                    return $q.reject(rejection);
                }
            };
        }])
        .config(['$httpProvider',
            function($httpProvider) {
                // Adds our debug interceptor to all $http requests
                $httpProvider.interceptors.push('phpDebugBarInterceptor');
            }
        ]);

})();