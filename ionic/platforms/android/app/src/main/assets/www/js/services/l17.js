/* global
 angular
 */
angular.module('starter').service('layout_17', function ($rootScope, $location, $timeout) {
    var service = {};

    service.getTemplate = function () {
        return 'templates/home/l17/view.html';
    };

    service.getModalTemplate = function () {
        return 'templates/home/modal/view.html';
    };

    service.onResize = function () {
        /** Double tap */
        $timeout(function () {
            service._resize();
            $timeout(function () {
                service._resize();
            }, 500);
        }, 100);
    };

    service.features = function (features, more_button) {
        var more_options = features.options.slice(12);
        var chunks = [];
        var i, j, temparray, chunk = 2;
        for (i = 0, j = more_options.length; i < j; i = i + chunk) {
            temparray = more_options.slice(i, i + chunk);
            chunks.push(temparray);
        }
        features.chunks = chunks;

        return features;
    };

    service._resize = function () {
        var scrollview = document.getElementById('metro-scroll');
        if (scrollview) {
            scrollview.style.display = 'block';
        }
        if (document.getElementById('metro-scroll') && document.getElementById('metro-line-2')) {
            var spacing = document.getElementById('metro-scroll').getBoundingClientRect().width / 100 * 2.5;
            var element = document.getElementById('metro-line-2');
            if (element) {
                var positionInfo = element.getBoundingClientRect();
                element.style.height = (positionInfo.width-spacing)/4+'px';
            }
            var lines = document.getElementsByClassName('metro-line');
            for (var i = 0; i < lines.length; i++) {
                lines[i].style.marginBottom = spacing+'px';
            }
        }
    };

    return service;
});
