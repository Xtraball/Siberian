String.prototype.nl2br = function () {
    return (this + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br />' + '$2');
};
String.prototype.startsWith = function (str) {
    return this.indexOf(str) == 0;
};
String.prototype.isEmpty = function () {
    var str = this.trim();
    return str == null || str == 'undefined' || str == '';
};

String.prototype.replaceAll = function (needle, haystack) {
    return this.replace(new RegExp(needle, 'g'), haystack);
};

String.prototype.toHex = function () {
    var rgb = this.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    return "#" +
        ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2);
};

var _hexToRgb = function (hex) {
    // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
    const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
    hex = hex.replace(shorthandRegex, (m, r, g, b) => {
        return r + r + g + g + b + b;
    });
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result
        ? [
            parseInt(result[1], 16),
            parseInt(result[2], 16),
            parseInt(result[3], 16),
        ]
        : null;
};

String.prototype.hexToRgb = function () {
    var hex = this.replace('#', '');
    var rgb = new Array();
    rgb['r'] = parseInt(hex.substring(0, 2), 16);
    rgb['g'] = parseInt(hex.substring(2, 4), 16);
    rgb['b'] = parseInt(hex.substring(4, 6), 16);
    return rgb;
};

Object.getSize = function (object) {
    var size = 0;
    if (typeof object == 'object') {
        for (var key in object) {
            if (object.hasOwnProperty(key)) size++;
        }
    }
    return size;
};

function hexToR(h) {
    return parseInt((cutHex(h)).substring(0, 2), 16)
}

function hexToG(h) {
    return parseInt((cutHex(h)).substring(2, 4), 16)
}

function hexToB(h) {
    return parseInt((cutHex(h)).substring(4, 6), 16)
}

function cutHex(h) {
    return (h.charAt(0) == "#") ? h.substring(1, 7) : h
}

$.fn.wait = function (time, type) {
    time = time || 1000;
    type = type || "fx";
    return this.queue(type, function () {
        var self = this;
        setTimeout(function () {
            $(self).dequeue();
        }, time);
    });
};

if (typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function () {
        return this.replace(/^\s+|\s+$/g, '');
    };
}

$.fn.blink = function (time) {
    time = time === undefined ? 100 : time;
    $(this).hide(50).delay(time).show(50);
};

var loader = {
    cpt: 0,
    timeout_id: null,
    init: function () {
        $('#hide_mask').click(function () {
            this.cpt = 0;
            this.hide();
        }.bind(this));
    },
    show: function (log) {
        if (typeof log == 'undefined') {
            log = 'inconnu';
        }
        if (this.timeout_id) {
            clearTimeout(this.timeout_id);
        }
        this.timeout_id = setTimeout(this.timeout.bind(this), 10000);
        $('#hide_mask').hide();
        this.cpt++;
        $('#mask').show();
    },
    hide: function (log) {
        if (typeof log == 'undefined') {
            log = 'inconnu';
        }
        if (--this.cpt <= 0) {
            this.cpt = 0;
            $('#mask').hide();
            if (this.timeout_id) {
                clearTimeout(this.timeout_id);
                this.timeout_id = null;
            }
        }
    },
    timeout: function () {
        $('#hide_mask').fadeIn();
    }
};

function reload(element, url, showLoader, success_callback, error_callback) {
    if (showLoader) {
        loader.show("reload");
    }
    let _element = $(element);
    let dataSet = {};
    if (_element.length) {
        dataSet = /form/i.test(_element.get(0).nodeName) ?
            _element.serializeArray() :
            _element.find("input, textarea, select").serializeArray();
    }

    $.ajax(url, {
        data: dataSet,
        dataType: "json",
        type: "POST",
        success: function (payload) {
            try {
                let _payload = payload;
                if (_payload.success && _payload.message) {
                    feature_form_success(_payload.message);

                    if (success_callback &&
                        typeof (success_callback) === "function") {
                        success_callback(_payload, _payload.message);
                    }
                    return;
                }

                // Redirects!
                if (_payload.url) {
                    window.location = _payload.url;
                } else {
                    if (success_callback &&
                        typeof (success_callback) === "function") {
                        success_callback(_payload, _payload.message);
                    }
                    if (_payload.html) {
                        element.parent().html(_payload.html);
                    }
                }
            } catch (e) {
                console.error(e);
                console.log('Trace for Unknown error in reload.success.');
                console.trace();
                if (e.message.indexOf('iframe.f.reloadTabbar') === -1) {
                    feature_form_error('Unknown error.');
                }
            }
        },
        error: function (payload) {
            try {
                let data = JSON.parse(payload.responseText);
                if (data.message) {
                    feature_form_error(data.message);
                }

            } catch (e) {
                // Silent catch!
                console.error(e);
                console.log('Trace for Unknown error in reload.error.');
                console.trace();
                feature_form_error("Unknown error.");
            }

            if (error_callback &&
                typeof (error_callback) === "function") {
                error_callback(payload);
            }
        }
    }).always(function () {
        if (showLoader) {
            loader.hide("reload");
        }
    });

}

let AlertMessage = Class.extend({
    type: 'success',
    init: function (message, addButton, timer) {
        // Migration to toastr!
        this.message = message;
        this.showLoader = true;
    },
    show: function () {
        if (this.showLoader) {
            loader.show('message');
        }
        switch (this.type) {
            case 'success':
                if (this.message === undefined || this.message.length === 0) {
                    this.message = "Success.";
                }
                toastr.success(
                    this.message,
                    null,
                    {
                        timeOut: 3700,
                        extendedTimeOut: 10000,
                        progressBar: true,
                        positionClass: "toast-top-center"
                    });
                break;
            case 'error':
                if (this.message === undefined || this.message.length === 0) {
                    this.message = "Unknown error.";
                }
                toastr.error(
                    this.message,
                    null,
                    {
                        timeOut: 3700,
                        extendedTimeOut: 10000,
                        progressBar: true,
                        positionClass: "toast-top-center"
                    });
                break;
        }
        return this;
    },

    hide: function () {
        // Toastr handles this
        return this;
    },
    didAppear: function () {
        // Toastr handles this
        return this;
    },
    didHide: function () {
        // Toastr handles this
        return this;
    },
    addLoader: function (addLoader) {
        this.showLoader = addLoader;
        return this;
    },
    addButton: function (addButton) {
        // Toastr handles this
        return this;
    },
    setTimer: function (timer) {
        if (timer) {
            timer *= 1000;
        }
        this.timer = timer;
        return this;
    },
    isError: function (isError) {
        this.type = (isError) ? 'error' : 'success';
        return this;
    },
    setMessage: function (message) {
        this.message = message;
    },
    isVisible: function () {
        // Toastr handles this
        return this;
    },
    setNoBackground: function (has_background) {
        // Toastr handles this
        return this;
    },
    reset: function () {
        this.type = 'success';
        return this;
    }
});

let message = new AlertMessage();

var Uploader = Class.extend({
    init: function () {
        this.current_pretty_photo_content = '';
    },
    showProgressbar: function () {
        feature_form_success(
            '<div>' + window.messages.uploadInProgress + '</div><div style="display:block" id="progressbar" class="progress"><div id="progress-label"></div></div>',
            {
                timeOut: 5000000,
                progressBar: false,
                tapToDismiss: false
            });
        $('#progressbar').progressbar({
            value: 100
        });
    },
    moveProgressbar: function (data) {
        var progress = Math.round((parseInt(data.loaded, 10) / parseInt(data.total, 10)) * 100);
        var progressLabel = $("#progress-label");
        $('#progressbar').progressbar({
            value: progress,
        });
        progressLabel.text( progress + " %" );
    },
    showError: function (errorMessage) {
        $('#progressbar').progressbar({
            value: 0
        });
        $('#progressbar').stop().hide();
        feature_form_error(errorMessage);
    },
    showSuccess: function (successMessage) {
        $('#progressbar').progressbar({
            value: 0
        });
        $('#progressbar').stop().hide();
        toastr.clear();
        feature_form_success(successMessage);
    },
    hide: function () {
        $('#progressbar').progressbar({
            value: 0
        });
        $('#progressbar').stop().hide();
        toastr.clear();
    },
    crop: function (params) {
        params['url'] += '/picture/' + params['file'];
        params['url'] += '/outputWidth/' + params['output_w'];
        params['url'] += '/outputHeight/' + params['output_h'];
        params['url'] += '/outputUrl/' + params['output_url'];
        if (params['quality']) {
            params['url'] += '/quality/' + params['quality'];
        }
        params['url'] += '/uploader/' + params['uploader'];
        if (params['option_value_id']) {
            params['url'] += '/option_value_id/' + params['option_value_id'];
        }
        if (params['is_colorizable']) {
            params['url'] += '/is_colorizable/' + params['is_colorizable'];
        }
        if (params['force_color']) {
            params['url'] += '/force_color/' + params['force_color'];
        }
        if (params['image_color']) {
            params['image_color'] = params['image_color'].replace('#', '');
            params['url'] += '/image_color/' + params['image_color'];
        }

        params['url'] += '?ajax=true';

        $.featherlight(params['url'], {
            type: 'ajax'
        });
    },
    callback: function () {
    },
    callbackDidFinish: function () {
    }
});

var Table = Class.extend({

    init: function (urls, params) {

        this.table = $("#table_list");
        this.reload_url = urls.reload_url;
        this.extract_url = urls.extract_url;

        this.filters = [];
        this.filters_form = $('#filters_form');

        this.extract_to = $('#extract_to_xls');


        this.params = {
            order: params.order,
            direction: params.direction,
            limit: params.limit,
            offset: params.offset
        };

        this.default_params = $.extend({}, this.params);

        this.pager = {
            links: $('#pager').find('a.page'),
            number_of_records: $('#number_of_records'),
            nbr_page: params.nbr_page,
            current_link: $('#pager').find('a.red'),
            new_link: null
        };

        if (this.pager.number_of_records.attr('id') && params.total_records != 'undefined') {
            var msg = "Nombre d'enregistrement%{s} : ".replace('%{s}', params.total_records > 1 ? 's' : '');
            this.pager.number_of_records.text(msg + parseInt(params.total_records));
        }

        $('#filters').find('input, select').each(function (e, el) {
            this.filters[this.filters.length] = $(el);
        }.bind(this));

        this.processEvents();

    },

    processEvents: function () {

        this.table.find('th > a.sortable').click(function (e) {

            if ($(e.target).attr('rel') == this.params.order) {
                this.params.direction = this.toggleDirection();
            } else {
                this.params.direction = 'asc';
            }

            this.params.order = $(e.target).attr('rel');

            this.reload();

            return false;
        }.bind(this));

        if (this.pager.links.length > 0) {
            this.pager.links.click(function (e) {
                var a = $(e.target);

                if (this.params.offset != a.text()) {
                    this.params.offset = a.text();
                    this.pager.new_link = a;
                    this.reload();
                }

                return false;
            }.bind(this));
        }

        if (this.filters_form.attr('id')) {
            this.filters_form.submit(function () {
                this.reload();
                return false;
            }.bind(this));
        }

        this.pager.links.each(function (e, element) {

            var link = $(element);

            if (this.pager.nbr_page <= 1) {
                link.parent('li').hide();
            } else if (link.text() > this.pager.nbr_page) {
                link.parent('li').hide();
            } else {
                link.parent('li').show();
            }

        }.bind(this));

        $('#limit').change(function () {
            this.params.limit = $('#limit').val();
            this.pager.new_link = $(this.pager.links[0]);
            this.params.offset = 1;
            this.reload();
        }.bind(this));

        this.extract_to.click(this.extractTo.bind(this));

        // Met à jour les couleurs des les lignes de la table
        var i = 0;
        this.table.find('tbody > tr').each(function (e, element) {
            var row = $(element);
            if (++i % 2) row.addClass('odd');
        });

        return this;

    },

    unbindAll: function () {

        this.table.find('th > a.sortable').unbind('click');
        this.pager.links.unbind('click');
        this.extract_to.unbind('click');

        $('#limit').unbind('change');

        if (this.filters_form.attr('id')) {
            this.filters_form.unbind('submit');
            if ($('#filters').is(':visible')) $('#filters').slideUp();
        }
        ;

        return this;
    },

    reload: function (params) {
        reload(this.table, this.getUrl(this.reload_url), true, this.reloadSuccess.bind(this), this.reloadFailed.bind(this));

        return this;
    },

    reloadSuccess: function (response) {
        if (!response.html) this.reloadFailed(response);
        else {
            if (this.pager.new_link) {
                this.pager.current_link.removeClass('bold red');
                this.pager.new_link.addClass('bold red');
            }

            this.unbindAll();
        }
    },

    reloadFailed: function (response) {
        alert('Une erreur est survenue lors du chargement. Merci de réessayer ultérieurement.');
        this.resetParams();
    },

    extractTo: function () {
        reload(this.table, this.getUrl(this.extract_url), true, this.downloadFile.bind(this));
        return false;
    },

    downloadFile: function (response) {
        if (response.url) {
            window.location = response.url;
        }
    },

    toggleDirection: function () {
        return this.params.direction == 'asc' ? 'desc' : 'asc';
    },

    getUrl: function (url) {

        var params = '';

        if (this.filters.length > 0) {
            var isFirst = true;
            for (var id in this.filters) {
                var filter = this.filters[id];
                if (filter.val()) {
                    if (params.length == 0) params += '?';
                    else params += '&';
                    params += '${id}=${value}'.replace('${id}', 'filters[' + filter.attr('id') + ']').replace('${value}', filter.val());
                    isFirst = false;
                }
            }

        }

        if (params.length > 0 && this.params.offset == this.default_params.offset) {
            this.pager.new_link = $(this.pager.links[0]);
            this.params.offset = 1;
        }

        if (this.params.order) url += '/order/' + this.params.order;
        if (this.params.direction) url += '/direction/' + this.params.direction;
        if (this.params.limit) url += '/limit/' + this.params.limit;
        if (this.params.offset) url += '/offset/' + this.params.offset;
        url += params;

        return url;

    },

    resetParams: function () {
        this.params = this.default_params;
        return this;
    }

});

function dateToStandardFormat(date) {
    return date.replace('-', '/').replace('-', '/');
}

$(document).ready(function () {
    bindScrollOverview();
});

function bindScrollOverview() {
    $(window).bind('scroll', function () {
        var navHeight = 60;
        var navHeightLeft = 93;
        if ($(window).scrollTop() > navHeight) {
            $('#iphone').addClass('phone-fixed');
        } else {
            $('#iphone').removeClass('phone-fixed');
        }

        if ($(window).scrollTop() > navHeightLeft) {
            $('#left-sidebar-wrapper,#right-sidebar-wrapper').addClass('fixed');
            $('#customization_label span').addClass('fixed');
        } else {
            $('#left-sidebar-wrapper,#right-sidebar-wrapper').removeClass('fixed');
            $('#customization_label span').removeClass('fixed');
        }
    });
}

/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/
