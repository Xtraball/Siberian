/**
 * Calculate distance between two coordinates.
 *
 * @param latitude_a
 * @param longitude_a
 * @param latitude_b
 * @param longitude_b
 * @param unit
 * @returns {number}
 */
window.calculateDistance = function (latitude_a, longitude_a, latitude_b, longitude_b, unit) {
    var radLatitudeA = Math.PI * latitude_a / 180;
    var radLatitudeB = Math.PI * latitude_b / 180;
    var theta = longitude_a - longitude_b;
    var radTheta = Math.PI * theta/180;
    var dist = Math.sin(radLatitudeA) * Math.sin(radLatitudeB) +
        Math.cos(radLatitudeA) * Math.cos(radLatitudeB) * Math.cos(radTheta);
    dist = Math.acos(dist);
    dist = dist * 180 / Math.PI;
    dist = dist * 60 * 1.1515;

    if (unit === 'K') {
        dist *= 1.609344;
    } else if (unit === 'N') {
        dist *= 0.8684;
    }

    return dist;
};

window.registerTap = function (numberOfTaps, callback) {
    var count = 0;
    var elements = new Map();

    document.addEventListener('click', function (event) {
        var countdown;

        function reset () {
            count = 0;
            countdown = null;
        }

        count += 1;

        if (count === numberOfTaps) {
            if (!elements.has(event.target)) {
                elements.set(event.target, 1);
            } else {
                var currentCount = elements.get(event.target);
                currentCount += 1;
                elements.set(event.target, currentCount);
            }

            var tripleClick = new CustomEvent('trplclick', {
                bubbles: true,
                detail: {
                    numberOfTripleClicks: elements.get(event.target)
                }
            });

            event.target.dispatchEvent(tripleClick);
            reset();
        }

        if (!countdown) {
            countdown = window.setTimeout(function () {
                reset();
            }, 500);
        }
    });

    document.addEventListener('trplclick', function () {
        callback();
    });
};

window.fileExists = function (path, callbackSuccess, callbackError) {
    window.requestFileSystem(window.LocalFileSystem.TEMPORARY, 0, function (fileSystem) {
        fileSystem.root.getFile(
            path,
            {
                create: false
            },
            function () {
                if (typeof callbackSuccess === 'function') {
                    callbackSuccess();
                }
            },
            function () {
                if (typeof callbackError === 'function') {
                    callbackError();
                }
            });
    }, function () {
        if (typeof callbackError === 'function') {
            callbackError();
        }
    });
};