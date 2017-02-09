App.factory('Video', function($rootScope, $sbhttp, $q, Url, Youtube) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("media/mobile_gallery_video_list/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        }).success(function(data) {
            if(data.displayed_per_page) {
                factory.displayed_per_page = data.displayed_per_page;
            }
        });
    };

    factory.find = function(item) {

        if(!this.value_id) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get("media/mobile_gallery_video_view/find", {gallery_id: item.id, offset: item.current_offset, value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findInYouTube = function(search_by, keyword, offset) {

        if(search_by == "user") {
            return Youtube.findByUser(keyword, offset);
        } else if(search_by == 'channel') {
            return Youtube.findByChannel(keyword, offset);
        } else {
            return Youtube.findBySearch(keyword, offset);
        }

    };

    return factory;
});
