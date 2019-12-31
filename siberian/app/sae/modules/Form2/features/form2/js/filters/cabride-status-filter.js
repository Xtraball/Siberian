angular.module('starter')
.filter("cabrideStatusFilter", function() {
    return function(inputArray, filterName) {
        // inprogress, torate, archived
        // "pending", "accepted", "declined", "done", "aborted", "expired"
        var statuses = [];
        switch (filterName) {
            case "inprogress":
                statuses = ["pending", "accepted", "onway", "inprogress"];
                break;
            case "torate":
                statuses = ["done"];
                break;
            case "archived":
                statuses = ["done", "expired", "aborted", "declined"];
                break;
        }
        var result;

        result = [];
        inputArray.forEach(function(input) {
            var status = input.status;
            if (statuses.indexOf(status) >= 0) {
                switch (filterName) {
                    case "inprogress":
                        result = result.concat([input]);
                        break;
                    case "torate":
                        if (parseInt(input.course_rating, 10) < 0) {
                            result = result.concat([input]);
                        }
                        break;
                    case "archived":
                        if (parseInt(input.course_rating, 10) > 0) {
                            result = result.concat([input]);
                        }
                        break;
                }
            }
        });

        return result;
    };
});