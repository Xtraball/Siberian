"use strict";

App.filter('duration', function () {
    return function (duration) {
        if (duration && duration != Infinity && !isNaN(duration)) {
            var totalSeconds = duration / 1000;
            var totalMinutes = totalSeconds / 60;
            var minutes = Math.round(totalMinutes % 60);
            if(minutes < 10) {
                minutes = '0' + minutes;
            }
            var seconds = Math.round(totalSeconds % 60);
            if (seconds < 10) {
                seconds = '0' + seconds;
            }
            return minutes + ':' + seconds;
        } else {
            return "00:00";
        }
    };
});