/*global
    CKEDITOR, $
 */

CKEDITOR.dialog.add('links', function(editor) {

    window.markup_links = [];
    parseLinks(window.feature_links, window.markup_links, 0, 0);

    window.selectedLink = null;

    window.selectLink = function(link) {
        var el = $(link);
        el.parents(".cke-dialog-links").find("a").removeClass("selected");
        el.addClass("selected");

        window.selectedLink = link;
    };

    $(document).off("keyup").on("keyup", ".cke-links-search-input", function() {
        var search = $(this);
        var value = search.val().toLowerCase().trim();
        var links = search.parent(".cke-links-search").next(".cke-dialog-links").find(".cke-feature-link");

        if(value.length === 0) {
            setTimeout(function() {
                links.removeClass("cke-highlight cke-parent-highlight");
                links.show();
            }, 100);
        } else {
            links.removeClass("cke-highlight cke-parent-highlight");
            links.hide();
            links.each(function() {
                var el = $(this);
                var filter = el.text().toLowerCase();

                if(filter.indexOf(value) > -1) {
                    el.addClass("cke-highlight").show();
                    var value_id = el.attr("data-valueid");
                    var level = el.attr("data-level");
                    el.prevAll("a[data-valueid='"+value_id+"']")
                        .not("[data-level='"+level+"']")
                        .addClass("cke-parent-highlight")
                        .show();
                }
            });
        }

    });

    function parseLinks(pack, markup, level, value_id) {
        pack.forEach(function(feature) {
            var feat            = (level === 0) ? feature[1][0] : feature;
            var label           = (level === 0) ? feature[0] : feature.label;
            var indent_label    = (level === 0) ? "" : "&nbsp;&nbsp;".repeat(level-1)+"└&nbsp;";

            var params = 'data-params=""';
            if(typeof feat.params !== "undefined") {
                var p = $.param(feat.params).replace(/=/g, ':').replace(/&/g, ",");

                params = 'data-params="'+p+'"';

                if(typeof feat.params.value_id !== "undefined") {
                    value_id = feat.params.value_id;
                }
            }

            var offline = false;
            if(typeof feat.offline !== "undefined") {
                offline = feat.offline;
            }

            markup.push('<a class="cke-feature-link" href="javascript:void(0);" onclick="selectLink(this);" data-level="'+level+'" data-valueid="'+value_id+'" data-label="'+label+'" data-state="'+feat["state"]+'" data-offline="'+offline+'" '+params+'>'+indent_label+label+'</a>');

            if(typeof feat.childrens !== "undefined") {
                parseLinks(feat.childrens, markup, level+1, value_id);
            }
        });

        return markup;
    }

    return {
        title: 'Insert link feature',
        minWidth: 200,
        minHeight: 180,
        contents: [
            {
                id: 'inAppLinks',
                elements: [
                    {
                        type: 'html',
                        html:
                        '<div class="cke-links-search">' +
                        '   <input type="text" class="cke-links-search-input cke_dialog_ui_input_text" name="cke-links-search" value="" placeholder="Filter ..." />' +
                        '</div>' +
                        '<div class="cke-dialog-links">' +
                        '   '+window.markup_links.join("")+'' +
                        '</div>'
                    }
                ]
            }
        ],
        onOk: function() {
            var dialog = this;
            var link = window.selectedLink;
            if(typeof link === "undefined") {
                return;
            }

            editor.insertHtml('<a data-state="'+link.attributes["data-state"].value+'" data-params="'+link.attributes["data-params"].value+'" data-offline="'+link.attributes["data-offline"].value+'">'+link.attributes["data-label"].value+'</a>');
        },
        onShow: function() {
            $(".cke-links-search-input").val("").trigger("keyup");
        }
    };
});