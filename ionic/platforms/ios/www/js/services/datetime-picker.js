/**
 * DatetimePicker
 *
 * @author Xtraball SAS
 * @version 4.16.6
 */
angular.module('starter').service('DatetimePicker', function ($ocLazyLoad, $translate, $q) {
    var service = {
        loadedPromise: $q.defer(),
        defaults: {
            headers: true,
            months: [
                $translate.instant("January", "datepicker"),
                $translate.instant("February", "datepicker"),
                $translate.instant("March", "datepicker"),
                $translate.instant("April", "datepicker"),
                $translate.instant("May", "datepicker"),
                $translate.instant("June", "datepicker"),
                $translate.instant("July", "datepicker"),
                $translate.instant("September", "datepicker"),
                $translate.instant("October", "datepicker"),
                $translate.instant("November", "datepicker"),
                $translate.instant("December", "datepicker")
            ],
            monthsShort: [
                $translate.instant("Jan.", "datepicker"),
                $translate.instant("Feb.", "datepicker"),
                $translate.instant("Mar.", "datepicker"),
                $translate.instant("Apr.", "datepicker"),
                $translate.instant("May.", "datepicker"),
                $translate.instant("Jun.", "datepicker"),
                $translate.instant("Jul.", "datepicker"),
                $translate.instant("Sep.", "datepicker"),
                $translate.instant("Oct.", "datepicker"),
                $translate.instant("Nov.", "datepicker"),
                $translate.instant("Dec.", "datepicker")
            ],
            weekDays: [
                $translate.instant("Sunday", "datepicker"),
                $translate.instant("Monday", "datepicker"),
                $translate.instant("Tuesday", "datepicker"),
                $translate.instant("Wednesday", "datepicker"),
                $translate.instant("Thursday", "datepicker"),
                $translate.instant("Friday", "datepicker"),
                $translate.instant("Saturday", "datepicker")
            ],
            weekDaysShort: [
                $translate.instant("Sun.", "datepicker"),
                $translate.instant("Mon.", "datepicker"),
                $translate.instant("Tue.", "datepicker"),
                $translate.instant("Wed.", "datepicker"),
                $translate.instant("Thu.", "datepicker"),
                $translate.instant("Fri.", "datepicker"),
                $translate.instant("Sat.", "datepicker")
            ],
            text: {
                title: $translate.instant("Pick a date and time", "datepicker"),
                cancel: $translate.instant("Cancel", "datepicker"),
                confirm: $translate.instant("OK", "datepicker"),
                year: $translate.instant("Year", "datepicker"),
                month: $translate.instant("Month", "datepicker"),
                day: $translate.instant("Day", "datepicker"),
                hour: $translate.instant("Hour", "datepicker"),
                minute: $translate.instant("Minute", "datepicker"),
                second: $translate.instant("Second", "datepicker"),
                millisecond: $translate.instant("Millisecond", "datepicker")
            }
        }
    };

    // Loading datetime picker
    $ocLazyLoad.load([
        "./dist/lazy/picker/picker.min.css",
        "./dist/lazy/picker/picker.js"
    ])
    .then(function () {
        Picker.setDefaults(service.defaults);

        service.loadedPromise.resolve();
    });

    service.isLoaded = function () {
        return service.loadedPromise.promise;
    };

    return service;
});