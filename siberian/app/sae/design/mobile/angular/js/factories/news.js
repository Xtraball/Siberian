
App.factory('News', function($rootScope, $http, Url, httpCache) {

    var factory = {
        value_id: null,
        displayed_per_page: null
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    factory.findAll = function(offset) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("comment/mobile_list/findall", {value_id: this.value_id, offset: offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
        });
    };

    factory.findNear = function(position) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("comment/mobile_list/findnear", {
                value_id: this.value_id,
                latitude: position.latitude,
                longitude: position.longitude
            }),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findAllPhotos = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("comment/mobile_gallery/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findAllLocation = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("comment/mobile_map/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.find = function(comment_id) {

        if(!this.value_id) return;

        var url = Url.get('comment/mobile_view/find', {comment_id: comment_id, value_id: this.value_id});

        return $http({
            method: 'GET',
            url: url,
            cache: !$rootScope.isOverview,
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

        return $http.post(url, data).success(function() {
            httpCache.remove(Url.get("comment/mobile_list/findall", {value_id: factory.value_id}));
        });
    };

    factory.flagPost = function(comment_id) {

        if(!this.value_id) return;

        var url = Url.get('comment/mobile_view/flagpost', {comment_id: comment_id, value_id: this.value_id});

        return $http({
            method: 'GET',
            url: url,
            cache: true,
            responseType:'json'
        });
    };

    factory.flagComment = function(answer_id) {

        if(!this.value_id) return;

        var url = Url.get('comment/mobile_view/flagcomment', {answer_id: answer_id, value_id: this.value_id});

        return $http({
            method: 'GET',
            url: url,
            cache: true,
            responseType:'json'
        });
    };

    factory.createComment = function(text, image, position) {

        if(!this.value_id) return;

        var url = Url.get("/comment/mobile_edit/create");
        var data = {
            value_id: this.value_id,
            text: text,
            image: image,
            position: position
        };

        return $http.post(url, data).success(function() {
            httpCache.remove(Url.get("comment/mobile_list/findall", {value_id: factory.value_id}));
        });
    };

    return factory;
});


App.factory('Answers', function ($rootScope, $http, Url, httpCache) {

    var factory = {};

    factory.comment_id = null;

    factory.findAll = function () {

        if (!this.comment_id) return;

        return $http({
            method: 'GET',
            url: Url.get("comment/mobile_answer/findall", {comment_id: this.comment_id}),
            cache: !$rootScope.isOverview,
            responseType: 'json'
        });
    };

    factory.add = function (answer) {

        if (!this.comment_id) return;

        var url = Url.get("comment/mobile_answer/add");
        var data = {
            comment_id: this.comment_id,
            text: answer
        };

        return $http.post(url, data).success(function() {
            httpCache.remove(Url.get("comment/mobile_list/findall", {value_id: factory.value_id}));
        });
    };

    return factory;
});
