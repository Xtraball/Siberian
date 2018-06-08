/*global
 App, angular
 */

/**
 * Youtube
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Youtube", function($q, $pwaRequest) {

    var factory = {
        key             : null,
        extendedOptions : {}
    };

    factory.findBySearch = function(keyword, offset) {

        var param_offset = "";
        
        if((offset !== null) && angular.isDefined(offset)) {
            param_offset = "&pageToken=" + offset;
        }

        var deferred = $q.defer();

        var google_url = "https://www.googleapis.com/youtube/v3/search/?q=" +
            keyword + "&type=video&part=snippet&key=" + factory.key + "&maxResults=5" +
            param_offset + "&order=date";

        $pwaRequest.get(google_url, {})
            .then(function(response) {
            
                var data = {
                    collection: []
                };

                angular.forEach(response.items, function (item) {

                    var video = {
                        video_id        : item.id.videoId,
                        cover_url       : item.snippet.thumbnails.medium.url,
                        title           : item.snippet.title,
                        description     : item.snippet.description,
                        date            : item.snippet.publishedAt,
                        url             : "https://www.youtube.com/watch?v=" + item.id.videoId,
                        url_embed       : "https://www.youtube.com/embed/" + item.id.videoId
                    };

                    data.collection.push(video);
                });

                data.nextPageToken = response.nextPageToken;

                return deferred.resolve(data);

            }, function (data) {
                return deferred.reject(data);
            });

        return deferred.promise;
    };

    factory.findByChannel = function(keyword, offset) {

        var param_offset = "";

        if((offset !== null) && angular.isDefined(offset)) {
            param_offset = "&pageToken=" + offset;
        }

        var deferred = $q.defer();

        var google_url = "https://www.googleapis.com/youtube/v3/channels/?part=snippet&key=" +
            factory.key + "&forUsername="+keyword + "&order=date";

        $pwaRequest.get(google_url)
            .then(function(data) {

            var id;
            if(data.items[0]) {
                id = data.items[0].id;
            } else {
                id = keyword;
            }

            var youtube_url = "https://www.googleapis.com/youtube/v3/search/?&part=snippet&key=" +
                factory.key + "&maxResults=5&type=video&channelId=" + id + param_offset + "&order=date";

            $pwaRequest.get(youtube_url)
                .then(function (response) {

                var data = {
                    collection: []
                };

                angular.forEach(response.items, function (item) {

                    var video = {
                        video_id        : item.id.videoId,
                        cover_url       : item.snippet.thumbnails.medium.url,
                        title           : item.snippet.title,
                        description     : item.snippet.description,
                        date            : item.snippet.publishedAt,
                        url             : "https://www.youtube.com/watch?v=" + item.id.videoId,
                        url_embed       : "https://www.youtube.com/embed/" + item.id.videoId
                    };

                    data.collection.push(video);

                });

                data.nextPageToken = response.nextPageToken;

                return deferred.resolve(data);

            }, function (data) {
                return deferred.reject(data);
            });

        });

        return deferred.promise;
    };

    factory.findByUser = function(keyword, offset) {
        
        var param_offset = "";

        if((offset !== null) && angular.isDefined(offset)) {
            param_offset = "&pageToken=" + offset;
        }

        var deferred = $q.defer();

        var youtube_url ="https://www.googleapis.com/youtube/v3/channels?part=contentDetails&key=" +
            factory.key + "&forUsername="+keyword + "&order=date";

        $pwaRequest.get(youtube_url)
            .then(function(data) {

            if(data.items[0] && data.items[0].contentDetails &&
                data.items[0].contentDetails.relatedPlaylists &&
                data.items[0].contentDetails.relatedPlaylists.uploads) {

                var google_url = "https://www.googleapis.com/youtube/v3/playlistItems/?&part=snippet&key=" +
                    factory.key + "&maxResults=5&playlistId=" +
                    data.items[0].contentDetails.relatedPlaylists.uploads + param_offset + "&order=date";

                $pwaRequest.get(google_url)
                    .then(function (response) {

                    var data = {
                        collection: []
                    };

                    angular.forEach(response.items, function(item) {

                        var video = {
                            video_id    : item.snippet.resourceId.videoId,
                            cover_url   : item.snippet.thumbnails.medium.url,
                            title       : item.snippet.title,
                            description : item.snippet.description,
                            date        : item.snippet.publishedAt,
                            url         : "https://www.youtube.com/watch?v=" + item.snippet.resourceId.videoId,
                            url_embed   : "https://www.youtube.com/embed/" + item.snippet.resourceId.videoId
                        };

                        data.collection.push(video);

                    });

                    data.nextPageToken = response.nextPageToken;

                    return deferred.resolve(data);

                }, function (data) {
                    return deferred.reject(data);
                });
            } else {
                return deferred.resolve(data);
            }

        });

        return deferred.promise;
    };

    return factory;
});
