/**
 *
 * Layout_Skeleton example
 *
 * All the following functions are required in order for the Layout to work
 */
angular.module('starter').service('layout_siberian_swipe', function ($rootScope, $timeout, $session) {
    var service = {};

    /**
     * Swiper instance
     *
     * @type {null}
     */
    var swipe_instance = null;

    /**
     * features array
     *
     * @type {null}
     */
    var _features = null;

    /**
     * Last clicked index
     *
     * @type {number}
     */
    service.last_index = 0;

    /**
     * Must return a valid template
     *
     * @returns {string}
     */
    service.getTemplate = function () {
        return 'modules/layout/home/layout_siberian_swipe/view.html';
    };

    /**
     * Must return a valid template
     *
     * @returns {string}
     */
    service.getModalTemplate = function () {
        return 'modules/layout/home/modal/view.html';
    };

    /**
     * onResize is used for css/js callbacks when orientation change
     */
    service.onResize = function () {
        var options = _features.layoutOptions;
        // Do nothing for this particular one!
        $session
            .getItem("swipeLastIndex")
            .then(function (lastIndex) {
                $timeout(function () {
                    if ((swipe_instance !== null) && (typeof swipe_instance.destroy === 'function')) {
                        swipe_instance.destroy(true, false);
                    }
                    swipe_instance = new Swiper('.layout.layout_siberian_swipe .swiper-container', {
                        direction: 'vertical',
                        loop: (options.loop === '1'),
                        effect: 'coverflow',
                        centeredSlides: true,
                        initialSlide: lastIndex,
                        slidesPerView: 'auto',
                        loopedSlides: 6,
                        coverflow: {
                            rotate: options.angle,
                            stretch: options.stretch,
                            depth: options.depth,
                            modifier: 1,
                            slideShadows: false
                        }
                    });
                }, 1);
            });

    };

    /**
     * Manipulate the features objects
     *
     * Examples:
     * - you can re-order features
     * - you can push/place the "more_button"
     *
     * @param features
     * @param more_button
     * @returns {*}
     */
    service.features = function (features, more_button) {
        /** Place more button at the end */
        _features = features;

        return features;
    };

    $rootScope.$on('OPTION_POSITION', function (event, args) {
        $timeout(function () {
            $session.setItem("swipeLastIndex", (args * 1) - 1);
        });
    });

    return service;
});
