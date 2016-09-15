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
    message.setTimer(2);
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
    if(typeof page != "undefined") {
    page.reload();
    }
};

var remove_row = function(rowid) {
    var row = $("#"+rowid);
    if(typeof row != "undefined") {
        var table = $(row.closest("table"));
        row.remove();

        var row_count = table.find("tbody tr").not(".edit-form").length;
        if(row_count <= 0) {
            page.reload();
        }
    }

};

/** Button picture uploader */
var button_picture_html = '<div class="feature-upload-placeholder" data-uid="%UID%">' +
    '   <img src="" data-uid="%UID%" />' +
    '   <button type="button" class="feature-upload-delete default_button btn color-blue" style="display: none;" data-uid="%UID%">' +
    '       <i class="icon-remove"></i>' +
    '   </button>' +
    '</div>';

var bindForms = function(default_parent) {

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

        /** Delegate the click */
        function handleInput() {
            var html = button_picture_html.replace(/%UID%/g, uid);

            $(default_parent+" input.feature-upload-input[data-uid='"+uid+"']").trigger("click");
            if($(default_parent+" div.feature-upload-placeholder[data-uid='"+uid+"']").length == 0) {
                $(default_parent+" button.feature-upload-button[data-uid='"+uid+"']").after(html);

                if($(default_parent+" input.feature-upload-hidden[data-uid='"+uid+"']").val() != "") {
                    var existing_file = $(default_parent+" input.feature-upload-hidden[data-uid='"+uid+"']").val();
                    $(default_parent+" div.feature-upload-placeholder[data-uid='"+uid+"']").find("img").attr("src", "/images/application"+existing_file);
                    $(default_parent+" button.feature-upload-delete[data-uid='"+uid+"']").show();
                }

                if($(default_parent+" button.feature-upload-button[data-uid='"+uid+"']").hasClass("is-required")) {
                    $(default_parent+" button.feature-upload-delete[data-uid='"+uid+"']").hide();
                }
            }

            /** Bind delete buttons */
            $(default_parent+" .feature-upload-delete[data-uid='"+uid+"']").on("click", function() {
                var uid = $(this).data("uid");

                $(default_parent+" #"+input).val("_delete_");
                $(default_parent+" .feature-upload-placeholder[data-uid='"+uid+"']").remove();

                return false;
            });
        }

        /** Existing files ! */
        handleInput();

        element.click(function() {
            handleInput();
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
                        if($(default_parent+" button.feature-upload-button[data-uid='"+uid+"']").hasClass("is-required")) {
                            $(default_parent+" button.feature-upload-delete[data-uid='"+uid+"']").hide();
                        }

                        $(default_parent+" #"+input).val(file);
                    }
                }
            }

        });
    });

    /** Handle form */
    var handleForm = function(form) {
        form = $(form);

        /** Confirm modal */
        if(typeof form.data("confirm") === "string" && !window.confirm(form.data("confirm"))) {
            return;
        }

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
                    if(data.errors) {
                        form.find("p.form-field-error").remove();
                        Object.keys(data.errors).forEach(function(key) {
                            if(form.find("#"+key).length > 0) {
                                form.find("#"+key).after("<p class=\"form-field-error\">"+data.errors[key]+"</p>");
                            }
                        });
                    }
                }
            },
            error: function() {
                feature_form_error("An error occured, please try again.");

            }
        });

        return false;
    };

    /** Bind forms */
    $(default_parent+" .feature-form.create, "+default_parent+" .feature-form.edit").on("submit", function(event) { event.preventDefault(); handleForm(this); });

    $(default_parent+" .feature-form.onchange").on("change", function(event) { event.preventDefault(); handleForm(this); });

    $(default_parent+" .feature-form.delete").on("submit", function(event) {
        event.preventDefault();
        var form = $(this);

        /** Confirm modal */
        if(typeof form.data("confirm") === "string" && !window.confirm(form.data("confirm"))) {
            return;
        }

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

    $.each(["create", "edit"], function(i, klass) {
    $(default_parent+" button.feature-back-button").on("click", function() {
        var el = $(this);

            el.closest(".feature-block-"+klass).slideUp(function() {
                el.closest(".feature-block-"+klass).prev(".feature-block-"+(klass == "create" ? "add" : "list")).slideDown();
            });
        });
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

    /** Table toggle edit */
    $(default_parent+' table .toggle-edit').on("click", function() {
        var el = $(this);
        var object_id = el.data("id");

        $("tr.edit-form[data-id="+object_id+"]").toggle();
    });

};




