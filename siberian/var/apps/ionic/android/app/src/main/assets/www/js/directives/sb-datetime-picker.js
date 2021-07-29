angular
.module("starter")
.directive("sbDatetimePicker", function (DatetimePicker) {
    return {
        restrict: "A",
        scope: {
            format: "=?",
            headers: "=?",
            model: "=?",
            title: "=?",
            skipDays: "=?",
            showWeekDays: "=?",
            showWeekDaysShort: "=?"
        },
        link: function (scope, element) {
            DatetimePicker
            .isLoaded()
            .then(function () {
                var options = angular.extend({}, DatetimePicker.defaults, {
                    headers: scope.headers || true,
                    skipDays: scope.skipDays || [],
                    showWeekDays: scope.showWeekDays || false,
                    showWeekDaysShort: scope.showWeekDaysShort || false,
                    format: scope.format || "YYYY-MM-DD HH:mm",
                    date: (scope.model && scope.model.length > 0) ? scope.model : null,
                    pick: function (e) {
                        scope.model = scope.pickerInstance.getDate(true);
                        scope.$apply();
                    }
                });

                scope.pickerInstance = new Picker(element[0], options);
                if (scope.title !== undefined) {
                    scope.pickerInstance.setTitle(scope.title);
                }
            });
        }
    };
});