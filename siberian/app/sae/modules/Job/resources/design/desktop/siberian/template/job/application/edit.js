/**
    If you need custom JS specific for your module:

    append this line at the end of your edit.phtml

    <script type="text/javascript" src="/app/local/modules/Job/resources/design/desktop/flat/template/job/application/edit.js"></script>

    note: don't forget to edit your paths

    and write custom JS right there
*/


$(document).ready(function() {
    bindForms("#list");

    $("table.sb-pager").sbpager({
        callback_goto_page: function() {
            $("table.sb-pager tr.edit-form[data-id]").hide()
        }
    });

    /** Simulate bootstrap tabs */
    $("ul[role='tablist'] a[role='tab']").on("click", function(e) {
        e.preventDefault();

        var el = $(this);
        var target = el.attr("aria-controls");

        $("ul[role='tablist'] li[role='presentation']").removeClass("active");
        el.parent("li").addClass("active");

        $("ul[role='tablist']").next("div.tab-content").find("div.tab-pane").hide();
        $("ul[role='tablist']").next("div.tab-content").find("div#"+target).show();
    });
});

