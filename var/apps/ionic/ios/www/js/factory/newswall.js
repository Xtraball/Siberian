App.factory('News', function($rootScope, $sbhttp, Url, httpCache) {

    var factory = {};

    factory.value_id = null;
    factory.displayed_per_page = null;

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        var params = {
            value_id: this.value_id,
            offset: offset
        };

        return $sbhttp({
            method: 'GET',
            url: Url.get("comment/mobile_list/findall", params),
            cache: $rootScope.isOffline,
            responseType:'json'
        });
    };

    factory.findNear = function(offset, position) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("comment/mobile_list/findnear", {
                value_id: this.value_id,
                offset: offset,
                latitude: position.latitude,
                longitude: position.longitude
            }),
            cache: false,
            responseType:'json'
        });
    };

    factory.findAllPhotos = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("comment/mobile_gallery/findall", {value_id: this.value_id}),
            cache: $rootScope.isOffline,
            responseType:'json'
        });
    };

    factory.findAllLocation = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("comment/mobile_map/findall", {value_id: this.value_id}),
            cache: $rootScope.isOffline,
            responseType:'json'
        });
    };

    factory.find = function(comment_id) {

        if(!this.value_id) return;

        var url = Url.get('comment/mobile_view/find', {comment_id: comment_id, value_id: this.value_id});

        return $sbhttp({
            method: 'GET',
            url: url,
            cache: $rootScope.isOffline,
            responseType:'json'
        });
    };

    factory.addLike = function(comment_id) {

        if(!this.value_id) return;

        var url = Url.get("/comment/mobile_view/addlike");
        var data = {
            comment_id: comment_id,
            value_id: this.value_id
        };

        return $sbhttp.post(url, data).success(function() {
            httpCache.remove(Url.get("comment/mobile_list/findall", {value_id: factory.value_id}));
        });
    };

    factory.flagPost = function(comment_id) {

        if(!this.value_id) return;

        var url = Url.get('comment/mobile_view/flagpost', {comment_id: comment_id, value_id: this.value_id});

        return $sbhttp({
            method: 'GET',
            url: url,
            cache: false,
            responseType:'json'
        });
    };

    factory.flagComment = function(answer_id) {

        if(!this.value_id) return;

        var url = Url.get('comment/mobile_view/flagcomment', {answer_id: answer_id, value_id: this.value_id});

        return $sbhttp({
            method: 'GET',
            url: url,
            cache: false,
            responseType:'json'
        });
    };

    factory.createComment = function(text, image, position) {

        if(!this.value_id) return;

        var url = Url.get("/comment/mobile_edit/create");
        var data = {
            value_id: this.value_id,
            text: text,
            image: image
        };

        if(position && position.latitude && position.longitude) {
            data.position = {
                latitude: position.latitude,
                longitude: position.longitude
            };
        }

        return $sbhttp.post(url, data).success(function() {
            httpCache.remove(Url.get("comment/mobile_list/findall", {value_id: factory.value_id}));
        });
    };

    return factory;
});


App.factory('Comment', function ($rootScope, $sbhttp, Url, httpCache) {

    var factory = {};

    factory.findAll = function (comment_id) {

        return $sbhttp({
            method: 'GET',
            url: Url.get("comment/mobile_comment/findall", {comment_id: comment_id}),
            cache: $rootScope.isOffline,
            responseType: 'json'
        });
    };

    factory.add = function (comment) {

        if (!comment.id) return;

        var url = Url.get("comment/mobile_comment/add");
        var data = {
            comment_id: comment.id,
            text: comment.text
        };

        return $sbhttp.post(url, data).success(function() {
            httpCache.remove(Url.get("comment/mobile_comment/findall", {comment_id: comment.id}));
        });

    };

    return factory;

});
