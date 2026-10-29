/**
 * DataTables 1.10 refuses to initialise a table twice: "Cannot reinitialise DataTable" is an alert() that stops the
 * editor page. Modules (Xdelivery, Restaurants ...) initialise their tables from templates that the editor loads
 * again on every visit of a tab, so the same table id is initialised more than once.
 * With destroy:true a second initialisation destroys the first one and builds the table again with the new options.
 */
(function ($) {
    "use strict";

    if (!$ || !$.fn || !$.fn.dataTable || !$.fn.dataTable.defaults) {
        return;
    }

    $.extend($.fn.dataTable.defaults, {
        destroy: true
    });
}(window.jQuery));
