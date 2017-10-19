
// Handle every elements for Forms on the Fly!
ckeditor_available_lang = ['af', 'ar', 'az', 'bg', 'bn', 'bs', 'ca', 'cs', 'cy', 'da', 'de-ch', 'de', 'el', 'en-au', 'en-ca', 'en-gb', 'en', 'eo', 'es', 'et', 'eu', 'fa', 'fi', 'fo', 'fr-ca', 'fr', 'gl', 'gu', 'he', 'hi', 'hr', 'hu', 'id', 'is', 'it', 'ja', 'ka', 'km', 'ko', 'ku', 'lt', 'lv', 'mk', 'mn', 'ms', 'nb', 'nl', 'no', 'oc', 'pl', 'pt-br', 'pt', 'ro', 'ru', 'si', 'sk', 'sl', 'sq', 'sr-latn', 'sr', 'sv', 'th', 'tr', 'tt', 'ug', 'uk', 'vi', 'zh-cn', 'zh'];

ckeditor_language = 'en';
if (ckeditor_available_lang.indexOf(datepicker_regional) !== -1) {
    ckeditor_language = datepicker_regional;
}

ckeditor_config = {};

ckeditor_config.default = {
    language: ckeditor_language,
    toolbar: [
        { name: 'source', items: ['Source'] },
        { name: 'insert', items: ['Image'] },
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
        { name: 'styles', items: ['TextColor', 'Format', 'FontSize'] }
    ],
    extraPlugins: 'codemirror',
    extraAllowedContent: 'a[*];img[*];'
};

ckeditor_config.cms = {
    language: ckeditor_language,
    toolbar: [
        { name: 'source', items: ['Source'] },
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
        { name: 'styles', items: ['TextColor', 'Format', 'FontSize'] },
        { name: 'links', items: ['Link', 'Unlink'] },
        { name: 'other', items: ['cmsimage', 'featurelink'] }
    ],
    extraPlugins: 'cmsimage,featurelink,codemirror',
    extraAllowedContent: 'a[*];img[*];iframe[*]'
};

ckeditor_config.complete = {
    language: ckeditor_language
};

var feature_picture_uploader = new Uploader();

(function ($) {
    var uniqueCntr = 0;
    $.fn.scrolled = function (waitTime, fn) {
        if (typeof waitTime === 'function') {
            fn = waitTime;
            waitTime = 10;
        }
        uniqueCntr = uniqueCntr + 1;
        var tag = 'scrollTimer' + uniqueCntr.toString();
        this.scroll(function () {
            var self = $(this);
            var timer = self.data(tag);
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(function () {
                self.removeData(tag);
                fn.call(self[0]);
            }, waitTime);
            self.data(tag, timer);
        });
    };
}(jQuery));

var handleFormError = function (form, data) {
    feature_form_error(data.message, data.message_timeout);
    if (data.errors) {
        form.find('p.form-field-error').remove();
        Object.keys(data.errors).forEach(function (key) {
            var input = form.find('#' + key);
            if (input.length > 0) {
                switch (input.attr('type')) {
                    case 'hidden':
                        // Search for button data-input!
                        var alt_input = form.find('[data-input=\'' + key + '\']');
                        if (alt_input.length > 0) {
                            input = alt_input;
                        }
                    default:
                        input.after('<p class="form-field-error">' + data.errors[key] + '</p>');
                        break;
                }
            }
        });
    }
};

var feature_form_error = function (html, timer) {
    message.addLoader(true);
    message.setNoBackground(false);
    message.isError(true);
    message.setMessage(html);
    message.show();
    message.addButton(true);
    var localTimer = (timer !== undefined) ? timer : 3;
    message.setTimer(localTimer);
};

var feature_form_success = function (html, timer) {
    message.addLoader(true);
    message.setNoBackground(false);
    message.isError(false);
    message.setMessage(html);
    message.show();
    message.addButton(false);
    var localTimer = (timer !== undefined) ? timer : 3;
    message.setTimer(localTimer);
};

var feature_reload = function () {
    if (typeof page !== 'undefined') {
        page.reload();
    }
};

var last_tab = -1;

var remove_row = function (rowid) {
    var row = $('#'+rowid);
    if (typeof row !== 'undefined') {
        var table = $(row.closest('table'));
        row.remove();

        var row_count = table.find('tbody tr').not('.edit-form').length;
        if (row_count <= 0) {
            page.reload();
        }
    }
};

var simpleget = function (uri) {
    loader.show('sb-simpleget');

    $.ajax({
        type: 'GET',
        url: uri,
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                feature_form_success(data.message || data.success_message, data.message_timeout);
            } else if (data.error) {
                feature_form_error(data.message, data.message_timeout);
            } else {
                feature_form_error('An error occured, please try again.', data.message_timeout);
            }

            loader.hide('sb-simpleget');
        },
        error: function (data) {
            feature_form_error('An error occured, please try again.', data.message_timeout);

            loader.hide('sb-simpleget');
        }
    });
};

var formget = function (uri, formData, callbackSuccess, callbackError, preventDefault) {
    loader.show('sb-formget');
    var localPreventDefault = (preventDefault === undefined) ? false : preventDefault;

    $.ajax({
        type: 'POST',
        url: uri,
        data: formData,
        dataType: 'json',
        success: function (data) {
            if (data.success) {
                if (localPreventDefault) {
                    feature_form_success(data.message || data.success_message, data.message_timeout);
                }

                if (typeof callbackSuccess === 'function') {
                    callbackSuccess(data);
                }
            } else if (data.error) {
                if (localPreventDefault) {
                    feature_form_error(data.message, data.message_timeout);
                }

                if (typeof callbackError === 'function') {
                    callbackError(data);
                }
            } else {
                if (localPreventDefault) {
                    feature_form_error('An error occured, please try again.', data.message_timeout);
                }

                if (typeof callbackError === 'function') {
                    callbackError(data);
                }
            }

            loader.hide('sb-formget');
        },
        error: function (data) {
            if (localPreventDefault) {
                feature_form_error(response.message, response.message_timeout);
            }

            if (typeof callbackError === 'function') {
                callbackError(response);
            }

            loader.hide('sb-formget');
        }
    });
};

var refreshCkeditor = function (element, key) {
    var el = $(element);
    var ck_key = (typeof key === 'undefined') ? el.attr('ckeditor') : key;
    var ck_fkey = (ckeditor_config.hasOwnProperty(ck_key)) ? ck_key : 'default';

    setTimeout(function () {
        el.ckeditor(
            function () {},
            ckeditor_config[ck_fkey]
        );
    }, 100);
};

/** Button picture uploader */
var button_picture_html = '<div class="feature-upload-placeholder" data-uid="%UID%">' +
    '   <img src="" data-uid="%UID%" />' +
    '   <button type="button" class="feature-upload-delete btn %FORMCOLOR%" style="display: none;" data-uid="%UID%">' +
    '       <i class="fa fa-times icon icon-remove"></i>' +
    '   </button>' +
    '</div>';

var bindForms = function (default_parent, color, success_cb, error_cb) {
    setTimeout(function () {
        _bindForms(default_parent, color, success_cb, error_cb);
    }, 200);
};

var _bindForms = function (default_parent, color, success_cb, error_cb) {
    var formColor = (color === undefined) ? 'color-blue' : color;

    $(default_parent+' .nav-tabs a[role=\'tab\']').on('click', function () {
        last_tab = $(this).attr('href');
    });

    if (last_tab !== -1) {
        $('.nav-tabs a[href=\''+last_tab+'\']').tab('show');
    }

    if (typeof datepicker_regional !== 'undefined') {
        $.datepicker.setDefaults($.datepicker.regional[datepicker_regional]);
    }

    if ($(default_parent).data('binded') === 'yes') {
        console.info(default_parent+' is already bound.');
        return;
    }

    $(default_parent).data('binded', 'yes');

    /** Clean-up form */
    var toggle_add_success = function () {
        $(default_parent+' .feature-block-create').toggle();
        $(default_parent+' .feature-block-add').toggle();

        /** Reset the form */
        $(default_parent+' form.feature-form').get(0).reset();
        $(default_parent+' form.feature-form').find('.feature-upload-placeholder').remove();
    };

    var handleRichtext = function () {
        /** Bind ckeditors (only visible ones) */
        $(default_parent+' .richtext').each(function () {
            var el = $(this);
            var ck_key = el.attr('ckeditor');
            var ck_config = (ck_key in ckeditor_config) ? ckeditor_config[ck_key] : ckeditor_config.default;

            setTimeout(function () {
                el.ckeditor(
                    function () {},
                    ck_config
                );
            }, 500);
        });
    };

    var handleDatetimePicker = function () {
        $(default_parent + ' input[data-datetimepicker]').each(function () {
            var el = $(this);
            if (typeof el.attr('data-hasdatepicker') === 'undefined') {
                el.attr('data-hasdatepicker', true);
                el.after('<input type="hidden" name="datepicker_format" value="' + datepicker_regional + '" />');

                var type = el.data('datetimepicker');
                var format = el.data('format');
                var options = {};
                if (format) {
                    options = {
                        'dateFormat': format
                    };
                }
                switch (type) {
                    default:
                    case 'datepicker':
                            el.datepicker(options);
                        break;
                    case 'timepicker':
                            el.timepicker(options);
                        break;
                    case 'datetimepicker':
                            el.datetimepicker(options);
                        break;
                }
            }
        });
    };

    handleRichtext();
    handleDatetimePicker();

    // Handle file uploads!
    $(default_parent+' button.feature-upload-button[data-uid]').each(function () {
        var element = $(this);
        var uid = element.data('uid');
        var input = element.data('input');
        var width = element.data('width');
        var height = element.data('height');

        // Delegate the click!
        function handleInput(prepare) {
            var html = button_picture_html
                .replace(/%FORMCOLOR%/g, formColor)
                .replace(/%UID%/g, uid);

            if (!prepare) {
                $(default_parent+' input.feature-upload-input[data-uid=\''+uid+'\']').trigger('click');
            }

            if ($(default_parent+' div.feature-upload-placeholder[data-uid=\''+uid+'\']').length == 0) {
                $(default_parent+' button.feature-upload-button[data-uid=\''+uid+'\']').after(html);

                if ($(default_parent+' input.feature-upload-hidden[data-uid=\''+uid+'\']').val() != '') {
                    var existing_file = $(default_parent+' input.feature-upload-hidden[data-uid=\''+uid+'\']').val();
                    $(default_parent+' div.feature-upload-placeholder[data-uid=\''+uid+'\']').find('img').attr('src', '/images/application'+existing_file);
                    $(default_parent+' button.feature-upload-delete[data-uid=\''+uid+'\']').show();
                }

                if ($(default_parent+' button.feature-upload-button[data-uid=\''+uid+'\']').hasClass('is-required')) {
                    $(default_parent+' button.feature-upload-delete[data-uid=\''+uid+'\']').hide();
                }
            }

            /** Bind delete buttons */
            $(default_parent+' .feature-upload-delete[data-uid=\''+uid+'\']').on('click', function () {
                var uid = $(this).data('uid');

                $(default_parent+' #'+input).val('_delete_').trigger('change');
                $(default_parent+' .feature-upload-placeholder[data-uid=\''+uid+'\']').remove();

                return false;
            });
        }

        /** Existing files ! */
        handleInput(true);

        element.on('click', function () {
            handleInput(false);
        });

        $(default_parent+' input.feature-upload-input[data-uid=\''+uid+'\']').fileupload({
            dataType: 'json',
            add: function (el, data) {
                $('.pp_content').attr('style', 'height: auto; width: 500px;');
                data.submit();
                feature_picture_uploader.showProgressbar();
            },
            progressall: function (el, data) {
                feature_picture_uploader.moveProgressbar(data);
            },
            done: function (el, data) {
                if (data.result.error) {
                    feature_picture_uploader.showError(data.result.message);
                }

                if (data.result.success) {
                    feature_picture_uploader.hide();

                    var params = [];
                    params.url = '/template/crop/crop';
                    params.file = data.result.files;
                    params.output_w = width;
                    params.output_h = height;
                    params.output_url = '$template$crop$validate';
                    params.uploader = 'feature_picture_uploader';

                    var $el = $(el.target);
                    if (typeof $el.attr('data-imagecolor') !== 'undefined') {
                        console.log($el.attr('data-imagecolor'));
                        params.image_color = $el.attr('data-imagecolor');
                        params.is_colorizable = true;
                    }

                    if (typeof $el.attr('data-forcecolor') !== 'undefined') {
                        params.force_color = true;
                    }

                    feature_picture_uploader.crop(params);
                    feature_picture_uploader.callback = function (file) {
                        var _file = file;
                        if (typeof file === 'object') {
                            _file = file.file;
                        }
                        $(default_parent+' div.feature-upload-placeholder[data-uid=\''+uid+'\']').find('img').attr('src', tmp_directory+'/'+_file);
                        $(default_parent+' button.feature-upload-delete[data-uid=\''+uid+'\']').show();
                        if ($(default_parent+' button.feature-upload-button[data-uid=\''+uid+'\']').hasClass('is-required')) {
                            $(default_parent+' button.feature-upload-delete[data-uid=\''+uid+'\']').hide();
                        }

                        $(default_parent+' #'+input).val(_file).trigger('change');
                    };
                }
            }

        });
    });

    /** Alias */
    var handleError = function (form, data) {
        handleFormError(form, data);
    };

    /** Handle form */
    var handleForm = function (form) {
        loader.show('sb-features');

        form = $(form);

        /** Confirm modal */
        if (typeof form.data('confirm') === 'string' && !window.confirm(form.data('confirm'))) {
            loader.hide('sb-features');
            return;
        }

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    feature_form_success(data.message || data.success_message, data.message_timeout);
                    if (form.hasClass('toggle')) {
                        var button = form.find('button[type=\'submit\']');
                        button.find('i').remove();
                        button.append((data.state === 1) ? button.data('toggle-off') : button.data('toggle-on'));
                        button.attr('title', (data.state === 1) ? button.data('title-off') : button.data('title-on'));
                        button.tooltip('destroy');
                        setTimeout(function () {
                            button.tooltip();
                        }, 500);
                    } else if (form.hasClass('onchange')) {
                        /** Do nothing */
                    } else if (form.hasClass('callback')) {
                        var callback = el.data('callback');
                        if (typeof callback !== 'undefined') {
                            try {
                                eval(callback);
                            } catch (e) {
                                console.log('unable to eval callback ' + callback);
                            }
                        }
                    } else if (form.hasClass('delete')) {
                        remove_row(form.data('rowid'));
                        $('tr.edit-form[data-id]').hide();
                    } else if (data.type) {
                        switch (data.type) {
                            case 'download':
                                if (data.url) {
                                    location.href = data.url;
                                }
                                break;
                            default:
                        }
                    } else {
                        feature_reload();
                    }
                } else if (data.error) {
                    handleError(form, data);
                }

                if (typeof success_cb === 'function') {
                    try {
                        success_cb(data);
                    } catch (e) {
                        console.log('An error occurred while executing the success callback.');
                    }
                }

                loader.hide('sb-features');
            },
            error: function (data) {
                var response = $.parseJSON(data.responseText);
                if (response.message !== '') {
                    handleError(form, response);
                } else {
                    feature_form_error('An error occured, please try again.', data.message_timeout);
                }

                if (typeof error_cb === 'function') {
                    try {
                        error_cb(data);
                    } catch (e) {
                        console.log('An error occurred while executing the error callback.');
                    }
                }

                loader.hide('sb-features');
            }
        });

        return false;
    };

    /** Bind forms */
    $(default_parent+' .feature-form.create, '+default_parent+' .feature-form.edit').on('submit', function (event) {
        event.preventDefault(); handleForm(this);
    });

    $(default_parent+' .feature-form.toggle').on('submit', function (event) {
        event.preventDefault(); handleForm(this);
    });

    $(default_parent+' .feature-form.onchange').on('change submit', function (event) {
        event.preventDefault(); handleForm(this);
    });

    $(default_parent+' .feature-form.delete').on('submit', function (event) {
        event.preventDefault(); handleForm(this);
    });


    /** Bind default buttons */
    $(default_parent+' button.feature-toggle-add').on('click', function () {
        var el = $(this);

        el.closest('.feature-block-add').next('.feature-block-create').toggle();
        el.closest('.feature-block-add').toggle();

        handleRichtext();
    });

    $.each(['create', 'edit'], function (i, klass) {
        $(default_parent+' button.feature-back-button').on('click', function () {
            var el = $(this);

            el.closest('.feature-block-'+klass).slideUp(function () {
                el.closest('.feature-block-'+klass).prev('.feature-block-'+(klass == 'create' ? 'add' : 'list')).slideDown();
            });
        });
    });

    $(default_parent+' .feature-toggle-items').on('click', function () {
        var el = $(this);
        el.closest('h3').next('.feature-manage-items').stop().slideToggle(300, function () {
            if ($(this).is(':visible')) {
                el.children('i').removeClass('fa-angle-down').addClass('fa-angle-up');
                el.children('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');
            } else {
                el.children('i').removeClass('fa-angle-up').addClass('fa-angle-down');
                el.children('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
            }
        });
    });

    /** Table toggle edit */
    $(default_parent+' table .open-edit').on('click', function () {
        var el = $(this);
        var object_id = el.data('id');
        var callback = el.data('callback');

        $('tr.edit-form[data-id!='+object_id+']').hide();

        var tr_edit = $('tr.edit-form[data-id='+object_id+']');

        /** Move the tr-edit right under the current object (avoiding conflicts with any search) */
        el.parents('tr').after(tr_edit);

        tr_edit.toggle();

        /** Load form if not present */
        if ($('tr.edit-form[data-id='+object_id+'] form').length == 0) {
            $('tr.edit-form[data-id='+object_id+'] p.close-edit').after('<div class="feature-loader loader-'+object_id+'"><img src="/app/sae/design/desktop/flat/images/customization/ajax/ajax-loader-black.gif"></div>');

            $.ajax({
                type: 'GET',
                url: el.data('form-url'),
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        $('tr.edit-form[data-id='+object_id+'] p.close-edit').after(data.form);
                        $('tr.edit-form[data-id='+object_id+'] .loader-'+object_id).remove();

                        setTimeout(function () {
                            bindForms('tr.edit-form[data-id='+object_id+']');
                            handleRichtext();
                            if (typeof callback !== 'undefined') {
                                try {
                                    eval(callback);
                                } catch (e) {
                                    console.log('unable to eval callback '+callback);
                                }
                            }
                        }, 100);
                    } else if (data.error) {
                        feature_form_error(data.message, data.message_timeout);
                    }
                },
                error: function () {
                    feature_form_error('An error occured, please try again.');
                }
            });
        } else {
            setTimeout(function () {
                bindForms('tr.edit-form[data-id='+object_id+']');
                handleRichtext();
                if (typeof callback !== 'undefined') {
                    try {
                        eval(callback);
                    } catch (e) {
                        console.log('unable to eval callback '+callback);
                    }
                }
            }, 100);
        }
    });

    /** Table toggle edit */
    $(default_parent+' table .close-edit').on('click', function () {
        var el = $(this);
        var object_id = el.data('id');
        var clear = (typeof el.data('clear') !== 'undefined');

        $('tr.edit-form[data-id]').hide();

        /** Clear if we want to use multiple forms, or just reload every-time */
        if (clear) {
            $('tr.edit-form[data-id='+object_id+']').removeData('binded');
            $('tr.edit-form[data-id='+object_id+'] p.close-edit').next('form').remove();
        }
    });

    /** Tooltip */
    $(default_parent+' .display_tooltip').tooltip();


    /** Range/Slider inputs with indicator */
    $('input[type=range].sb-slider').on('change input', function () {
        var el = $(this);
        if (el.next('.range-indicator').length) {
            el.next('.range-indicator').find('span.value').text(el.val());
        }
    });

    /** Bind presets */
    $(default_parent+' form#form-options').each(function () {
        var form = $(this);
        if ($('div[data-form=\''+form.attr('id')+'\']').length) {
            var presets = $('div[data-form=\''+form.attr('id')+'\']');
            presets.find('a').on('click', function () {
                var preset = $(this);
                $.each(preset.data(), function (name, value) {
                    form.find('[name=\''+name+'\']').each(function () {
                        var el = $(this);
                        switch (el.attr('type')) {
                            default:
                                el.val(value).trigger('change');
                                break;
                            case 'hidden':
                                break;
                            case 'checkbox':
                                el.prop('checked', value).trigger('change');
                                break;
                        }
                    });
                });
                form.submit();
            });
        }
    });
};

// Special search/filter
var initSearch = function (input, clear, empty, itemsClass, fnCallback) {
    var self = this;

    self.input = $(input);
    self.clear = $(clear);
    self.empty = $(empty);
    self.itemsClass = itemsClass;
    self.fnCallback = fnCallback;
    self.isFolder = false;
    self.folderId = null;

    self.doSearch = function (text, searchFnCallback) {
        // Restore!
        if (text.length === 0) {
            self.empty.addClass('active');
            self.clear.removeClass('active');
        } else {
            self.empty.removeClass('active');
            self.clear.addClass('active');
        }

        // split only once!
        var textParts = text.split(' ');

        $(self.itemsClass).each(function (index, item) {
            var element = $(item);
            var textValue = element.data('search');
            var push = true;
            textParts.forEach(function (part) {
                if (textValue.indexOf(part) === -1) {
                    push = false;
                }
            });

            var globalPush = (
                (push && !self.isFolder && ((element.attr('rel') === '') || (element.attr('rel') === undefined))) ||
                (push && self.isFolder && (element.attr('rel') === self.folderId) && (element.attr('rel') !== ''))
            );

            if (globalPush) {
                element.show();
            } else {
                element.hide();
            }
        });

        if (typeof self.fnCallback === 'function') {
            console.log(self.fnCallback);
            setTimeout(self.fnCallback(), 100);
        }

        if (typeof searchFnCallback === 'function') {
            setTimeout(searchFnCallback(), 100);
        }
    };

    self.input.on('keyup', function () {
        self.doSearch($(this).val().trim().toLowerCase(), null);
    });

    self.clear.on('click', function () {
        self.input.val('');
        self.doSearch('', null);
    });

    return {
        doSearch: function (text, doSearchFnCallback) {
            self.input.val(text);
            self.doSearch(text, doSearchFnCallback);
        },
        clearSearch: function (clearSearchFnCallback) {
            self.input.val('');
            self.doSearch('', clearSearchFnCallback);
        },
        setIsFolder: function (isFolder) {
            self.isFolder = isFolder;
        },
        setFolderId: function (folderId) {
            self.folderId = folderId;
        }
    };
};

// Global Eventer for overview!
try {
    var eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';
    var eventListener = window[eventMethod];
    var messageEvent = (eventMethod === 'attachEvent') ? 'onmessage' : 'message';

    // Listen to message from child window
    iframeLoaded = new Promise(function (resolve, reject) {
        eventListener(messageEvent, function (e) {
            switch (e.data) {
                case 'overview.loaded':
                    resolve();
                    break;
            }
        }, false);
    });
} catch (e) {
    // No luck, no promise!
}


