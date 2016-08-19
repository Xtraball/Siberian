/** Handle every elements for Forms on the Fly */
var default_ckeditor_config = {
    language: 'en',
    toolbar: [
        {name: 'source', items: ['Source']},
        {name: 'insert', items: ['Image']},
        {
            name: 'basicstyles',
            groups: ['basicstyles', 'cleanup'],
            items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']
        },
        {
            name: 'paragraph',
            groups: ['indent', 'align'],
            items: ['Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
        },
        {name: 'styles', items: ['TextColor', 'Format']}
    ]
};

var feature_picture_uploader = new Uploader();

var feature_form_error = function(html) {
    message.addLoader(true);
    message.setNoBackground(false);
    message.isError(true);
    message.setMessage(html);
    message.show();
    message.addButton(true);
    message.setTimer(4);
};

var feature_form_success = function(html) {
    message.addLoader(true);
    message.setNoBackground(false);
    message.isError(false);
    message.setMessage(html);
    message.show();
    message.addButton(true);
    message.setTimer(2);
};

var feature_reload = function() {
    page.reload();
};

var remove_row = function(rowid) {
    var row = $("#"+rowid);
    if(typeof row != "undefined") {
        var table = $(row.closest("table"));
        row.remove();

        var row_count = table.find("tbody tr").length;
        if(row_count <= 0) {
            page.reload();
        }
    }

};

/** Button picture uploader */
var button_picture_html = '<div class="feature-upload-placeholder" data-uid="%UID%">' +
    '   <img src="" data-uid="%UID%" />' +
    '   <button type="button" class="feature-upload-delete btn color-blue" style="display: none;" data-uid="%UID%">' +
    '       <i class="fa fa-times"></i>' +
    '   </button>' +
    '</div>';

var bindForms = function(parent) {

    var default_parent = (typeof parent == "undefined") ? "#page" : parent;

    /** Clean-up form */
    var toggle_add_success = function() {
        $(default_parent+" .feature-block-create").toggle();
        $(default_parent+" .feature-block-add").toggle();

        /** Reset the form */
        $(default_parent+" form.feature-form").get(0).reset();
        $(default_parent+" form.feature-form").find(".feature-upload-placeholder").remove();
    };

    /** Bind ckeditors */
    $(default_parent+' .richtext').ckeditor(
        function(){},
        default_ckeditor_config
    );

    /** Handle file uploads */
    $(default_parent+" button.feature-upload-button[data-uid]").each(function() {

        var element = $(this);
        var uid = element.data("uid");
        var input = element.data("input");
        var width = element.data("width");
        var height = element.data("height");

        element.click(function() {
            /** Delegate the click */
            var html = button_picture_html.replace(/%UID%/g, uid);

            $(default_parent+" input.feature-upload-input[data-uid='"+uid+"']").trigger("click");
            if($(default_parent+" div.feature-upload-placeholder[data-uid='"+uid+"']").length == 0) {
                $(default_parent+" button.feature-upload-button[data-uid='"+uid+"']").after(html);
            }


            /** Bind delete buttons */
            $(default_parent+" .feature-upload-delete[data-uid='"+uid+"']").on("click", function() {
                var uid = $(this).data("uid");

                $(default_parent+" #"+input).val("");
                $(default_parent+" .feature-upload-placeholder[data-uid='"+uid+"']").remove();

                return false;
            });
        });

        $(default_parent+" input.feature-upload-input[data-uid='"+uid+"']").fileupload({
            dataType: "json",
            add: function (el, data) {
                $('.pp_content').attr('style', 'height: auto; width: 500px;');
                data.submit();
                feature_picture_uploader.showProgressbar();
            },
            progressall: function (el, data) {
                feature_picture_uploader.moveProgressbar(data);
            },
            done: function (el, data) {
                if(data.result.error) {
                    feature_picture_uploader.showError(data.result.message);
                }

                if(data.result.success) {
                    feature_picture_uploader.hide();

                    var params = new Array();
                    params["url"] = "/template/crop/crop";
                    params["file"] = data.result.files;
                    params["output_w"] = width;
                    params["output_h"] = height;
                    params["output_url"] = "$template$crop$validate";
                    params["uploader"] = 'feature_picture_uploader';

                    feature_picture_uploader.crop(params);
                    feature_picture_uploader.callback = function(file) {
                        $(default_parent+" div.feature-upload-placeholder[data-uid='"+uid+"']").find("img").attr("src", tmp_directory+"/"+file);
                        $(default_parent+" button.feature-upload-delete[data-uid='"+uid+"']").show();
                        $(default_parent+" #"+input).val(file);
                    }
                }
            }

        });
    });

    /** Bind forms */
    $(default_parent+" .feature-form.create").on("submit", function() {
        var form = $(this);

        $.ajax({
            type: "POST",
            url: form.attr("action"),
            data: form.serialize(),
            dataType: "json",
            success: function(data) {
                if(data.success) {
                    feature_form_success(data.message || data.success_message);
                    feature_reload();

                } else if(data.error) {
                    feature_form_error(data.message);

                }
            },
            error: function() {
                feature_form_error("An error occured, please try again.");

            }
        });

        return false;
    });

    $(default_parent+" .feature-form.delete").on("submit", function() {
        var form = $(this);

        $.ajax({
            type: "POST",
            url: form.attr("action"),
            data: form.serialize(),
            dataType: "json",
            success: function(data) {
                if(data.success) {
                    feature_form_success(data.message || data.success_message);
                    remove_row(form.data("rowid"));

                } else if(data.error) {
                    feature_form_error(data.message);

                }
            },
            error: function() {
                feature_form_error("An error occured, please try again.");

            }
        });

        return false;
    });


    /** Bind default buttons */
    $(default_parent+" button.feature-toggle-add").on("click", function() {
        var el = $(this);

        el.closest(".feature-block-add").next(".feature-block-create").toggle();
        el.closest(".feature-block-add").toggle();
    });

    $(default_parent+" button.feature-back-button").on("click", function() {
        var el = $(this);

        el.closest(".feature-block-create").prev(".feature-block-add").toggle();
        el.closest(".feature-block-create").toggle();
    });

    $(default_parent+' .feature-toggle-items').on("click", function() {
        var el = $(this);
        el.closest("h3").next(".feature-manage-items").stop().slideToggle(300, function() {
            if($(this).is(':visible')) {
                el.children('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
            else {
                el.children('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        });
    });

};




