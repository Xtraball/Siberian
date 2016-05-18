App.service("Url", function() {
    return {
        get: function(uri, params) {
            var url = new Array();
            url.push(BASE_URL);
            url.push(uri);
            for(var i in params) {
                if(angular.isDefined(params[i])) {
                    url.push(i);
                    url.push(params[i]);
                }
            }

            url = url.join('/');
            if(url.substr(0, 1) != "/") url = "/"+url;

            return url;
        }
    }
});