App.service("Url", function($rootScope) {

    var _that = this;
    this.__base_url = BASE_URL;
    _that.__base_url_parts = this.__base_url.length <= 1 ? new Array() : this.__base_url.split("/");

    this.__sanitize = function(str) {

        if(str.startsWith("/")) {
            str = str.substr(1, str.length - 1);
        }

        return str;
    }

    this.__base_url = this.__sanitize(this.__base_url);

    return {
        get: function(uri, params) {

            uri = _that.__sanitize(uri);

            var url = new Array();
            url = url.concat(_that.__base_url_parts);
            url.push(uri);

            for(var i in params) {
                if(angular.isDefined(params[i])) {
                    url.push(i);
                    url.push(params[i]);
                }
            }

            url = url.join('/');
            if(!url.startsWith("/")) url = "/"+url;

            return url;
        }
    }
});