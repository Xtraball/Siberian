App.directive("datePicker", function(){
    return{
        restrict: "A",
        require: "ngModel",
        link: function(scope, element, attr, ctrl){

            $.datepicker.setDefaults($.datepicker.regional[attr.locale]);

            // Format date on load
            ctrl.$formatters.unshift(function(value) {
                if(value && moment(value).isValid()){
                    return moment(new Date(value)).format("MM/DD/YYYY");
                }
                return value;
            });

            //Disable Calendar
            scope.$watch(attr.ngDisabled, function (newVal) {
                if (newVal === true) {
                    $(element).datepicker("disable");
                } else {
                    $(element).datepicker("enable");
                }
            });

            // Datepicker Settings
            element.datepicker({
                autoSize: true,
                changeYear: true,
                changeMonth: true,
                dateFormat: attr["dateformat"] || "mm/dd/yy",
                showOn: "both",
                buttonText: '<i class="picker-button-1 fa fa-calendar"></i>',
                onSelect: function (valu) {
                    scope.$apply(function () {
                        ctrl.$setViewValue(valu);
                    });
                    element.focus();
                },
                beforeShow: function(){
                    if (attr["minDate"] !== null) {
                        $(element).datepicker("option", "minDate", attr["minDate"]);
                    }

                    if (attr["maxDate"] !== null) {
                        $(element).datepicker("option", "maxDate", attr["maxDate"]);
                    }
                }
            });
        }
    }
});