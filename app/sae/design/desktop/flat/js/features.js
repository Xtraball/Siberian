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
    message.addButton(false);
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
    '   <button type="button" class="feature-upload-delete btn color-blue" style="display: none;" data-uid="%UID%">' +
    '       <i class="fa fa-times icon icon-remove"></i>' +
    '   </button>' +
    '</div>';

var bindForms = function(default_parent) {

    if($(default_parent).data("binded") == "yes") {
        console.info(default_parent+" is already bound.");
        return;
    } else {
        $(default_parent).data("binded", "yes");
    }


    /** Clean-up form */
    var toggle_add_success = function() {
        $(default_parent+" .feature-block-create").toggle();
        $(default_parent+" .feature-block-add").toggle();

        /** Reset the form */
        $(default_parent+" form.feature-form").get(0).reset();
        $(default_parent+" form.feature-form").find(".feature-upload-placeholder").remove();
    };



    var handleRichtext = function() {
        /** Bind ckeditors (only visible ones) */
        $(default_parent+' .richtext:visible').ckeditor(
            function(){},
            default_ckeditor_config
        );
    };

    var handleError = function(form, data) {
        feature_form_error(data.message);
        if(data.errors) {
            form.find("p.form-field-error").remove();
            Object.keys(data.errors).forEach(function(key) {
                var input = form.find("#"+key);
                if(input.length > 0) {
                    switch(input.attr("type")) {
                        case "hidden":
                            /** Search for button data-input */
                            var alt_input = form.find("[data-input='"+key+"']");
                            if(alt_input.length > 0) {
                                input = alt_input;
                            }
                        default:
                            input.after("<p class=\"form-field-error\">"+data.errors[key]+"</p>");
                            break;
                    }
                }
            });
        }
    };

    handleRichtext();

    /** Handle file uploads */
    $(default_parent+" button.feature-upload-button[data-uid]").each(function() {

        var element = $(this);
        var uid = element.data("uid");
        var input = element.data("input");
        var width = element.data("width");
        var height = element.data("height");

        /** Delegate the click */
        function handleInput(prepare) {
            var html = button_picture_html.replace(/%UID%/g, uid);

            if(!prepare) {
                $(default_parent+" input.feature-upload-input[data-uid='"+uid+"']").trigger("click");
            }

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
        handleInput(true);

        element.on("click", function() {
            handleInput(false);
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
                    if(form.hasClass("toggle")) {
                        var button = form.find("button[type='submit']");
                        button.find("i").remove();
                        button.append((data.state == 1) ? button.data("toggle-off") : button.data("toggle-on"));
                        button.attr("title", (data.state == 1) ? button.data("title-off") : button.data("title-on"));
                        button.tooltip("destroy");
                        setTimeout(function() {
                            button.tooltip();
                        }, 500);
                    } else if(form.hasClass("onchange")) {
                        /** Do nothing */
                    } else if(form.hasClass("delete")) {
                        remove_row(form.data("rowid"));
                        $("tr.edit-form[data-id]").hide();
                    } else {
                        feature_reload();
                    }


                } else if(data.error) {
                    handleError(form, data);
                }
            },
            error: function(data) {
                var response = $.parseJSON(data.responseText);
                if(response.message != "") {
                    handleError(form, response);
                } else {
                    feature_form_error("An error occured, please try again.");
                }

            }
        });

        return false;
    };

    /** Bind forms */
    $(default_parent+" .feature-form.create, "+default_parent+" .feature-form.edit").on("submit", function(event) { event.preventDefault(); handleForm(this); });

    $(default_parent+" .feature-form.toggle").on("submit", function(event) { event.preventDefault(); handleForm(this); });

    $(default_parent+" .feature-form.onchange").on("change", function(event) { event.preventDefault(); handleForm(this); });

    $(default_parent+" .feature-form.delete").on("submit", function(event) { event.preventDefault(); handleForm(this); });


    /** Bind default buttons */
    $(default_parent+" button.feature-toggle-add").on("click", function() {
        var el = $(this);

        el.closest(".feature-block-add").next(".feature-block-create").toggle();
        el.closest(".feature-block-add").toggle();

        handleRichtext();
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
                el.children('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');
            }
            else {
                el.children('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                el.children('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
            }
        });
    });

    /** Table toggle edit */
    $(default_parent+' table .open-edit').on("click", function() {
        var el = $(this);
        var object_id = el.data("id");

        $("tr.edit-form[data-id!="+object_id+"]").hide();
        $("tr.edit-form[data-id="+object_id+"]").toggle();

        /** Load form if not present */
        if($("tr.edit-form[data-id="+object_id+"] form").length == 0) {

            $("tr.edit-form[data-id="+object_id+"] p.close-edit").after("<div class=\"feature-loader loader-"+object_id+"\"><img src=\"/app/sae/design/desktop/flat/images/customization/ajax/ajax-loader-black.gif\"></div>");

            $.ajax({
                type: "GET",
                url: el.data("form-url"),
                dataType: "json",
                success: function(data) {
                    if(data.success) {
                        $("tr.edit-form[data-id="+object_id+"] p.close-edit").after(data.form);
                        $("tr.edit-form[data-id="+object_id+"] .loader-"+object_id).remove();

                        setTimeout(function() {
                            bindForms("tr.edit-form[data-id="+object_id+"]");
                            handleRichtext();
                        }, 100);

                    } else if(data.error) {
                        feature_form_error(data.message);
                    }
                },
                error: function() {
                    feature_form_error("An error occured, please try again.");
                }
            });
        } else {
            setTimeout(function() {
                bindForms("tr.edit-form[data-id="+object_id+"]");
                handleRichtext();
            }, 100);
        }

    });

    /** Table toggle edit */
    $(default_parent+' table .close-edit').on("click", function() {
        var el = $(this);
        var object_id = el.data("id");

        $("tr.edit-form[data-id]").hide();
    });

    /** Tooltip */
    $(default_parent+' .display_tooltip').tooltip();

    $(default_parent+' .sb-form-checkbox').each(function() {
        var el = $(this);
        if(!el.hasClass('flatbox')) {
            el.parent().addClass("control control--checkbox");
            el.parent().append('<div class="color-blue control__indicator"></div>');
            el.addClass('flatbox');
        }
    });

    $(default_parent+' .sb-form-radio').each(function() {
        var el = $(this);
        if(!el.hasClass('flatbox')) {
            el.parent().addClass("control control--radio");
            el.parent().append('<div class="color-blue control__indicator"></div>');
            el.addClass('flatbox');
        }
    });

};