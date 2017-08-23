/*global
 angular, ProgressBar
 */

/**
 * ProgressBar
 *
 * @author Xtraball SAS
 *
 * @note wrapper to lazyload/get progressbar js
 */
angular.module('starter').service('ProgressbarService', function ($ocLazyLoad) {
    var service = {
        config: {
            trail: '#eee',
            bar_text: '#aaa'
        },
        progress_bar: null
    };

    service.init = function (config) {
        service.config = config;

        return $ocLazyLoad.load('./js/libraries/progressbar.min.js');
    };

    service.createCircle = function (container) {
        service.progress_bar = new ProgressBar.Circle(container, {
            color: service.config.bar_text,
            strokeWidth: 2.6,
            trailWidth: 2,
            trailColor: service.config.trail,
            easing: 'easeInOut',
            duration: 1000,
            text: {
                autoStyleContainer: false
            },
            from: {
                color: service.config.bar_text,
                width: 2.6
            },
            to: {
                color: service.config.bar_text,
                width: 2.6
            },
            step: function (state, circle) {
                circle.path.setAttribute('stroke', state.color);
                circle.path.setAttribute('stroke-width', state.width);

                var value = Math.round(circle.value() * 100);
                if (value === 0) {
                    circle.setText('');
                } else {
                    circle.setText(value);
                }
            }
        });
    };

    /**
     *
     * @param progress 0-1
     */
    service.updateProgress = function (progress) {
        if (service.progress_bar !== null) {
            service.progress_bar.animate(progress);
        }
    };

    service.remove = function () {
        if (service.progress_bar !== null) {
            service.progress_bar.destroy();
            service.progress_bar = null;
        }
    };

    return service;
});
