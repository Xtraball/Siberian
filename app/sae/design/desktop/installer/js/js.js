
var loader = {
    cpt: 0,
    timeout_id: null,
    init: function() {
        $('#hide_mask').click(function() {
            this.cpt=0;
            this.hide();
        }.bind(this));
    },
    show: function(log) {
        if(typeof log == 'undefined') log = 'inconnu';
        console.log('show ' + log);
        if(this.timeout_id) clearTimeout(this.timeout_id);
        this.timeout_id = setTimeout(this.timeout.bind(this), 10000);
        $('#hide_mask').hide();
        this.cpt++;
        $('#mask').show();
        console.log(this.cpt)
    },
    hide: function(log) {
        if(typeof log == 'undefined') log = 'inconnu';
        console.log('hide ' + log);
        if(--this.cpt <= 0) {
            this.cpt = 0;
            $('#mask').hide();
            if(this.timeout_id) {
                clearTimeout(this.timeout_id);
                this.timeout_id = null;
            }
        }
        console.log(this.cpt)
    },
    timeout: function() {
        $('#hide_mask').fadeIn();
    }
}

function reload(element, url, showLoader, success_callback, error_callback) {
    if(showLoader) loader.show('reload');
    element = $(element);
    var datas = {};
    if(element.length) {
        datas = /form/i.test(element.get(0).nodeName) ? element.serializeArray() : element.find('input, textarea, select').serializeArray();
    }

    $.post(url,
        datas,
        function(data) {

            if(data.message || data.success_message) {
//                if(typeof message == 'undefined') var message = new AlertMessage();
                message.setMessage(data.message ? data.message : data.success_message);
                message.addButton(data.message_button ? true : false);
                message.setTimer(data.message_timeout ? data.message_timeout : false);
                message.addLoader(data.message_loader == 0 ? false : true);
                message.isError(data.message ? true : false);
                message.show();
            }

            if(data.message) {
                if(error_callback && typeof(error_callback) === "function") error_callback(data, message);
                return;
            }
            if(data.url) {
                window.location = data.url;
            }
            else {
                if(success_callback && typeof(success_callback) === "function") success_callback(data, message);
                if(data.html) element.parent().html(data.html);
            }

//            if(data.success_message) {
//                message.setMessage(data.success_message);
//                message.isError();
//                message.show();
//            }

        },
        'json'
    )
    .error(function(xhr, ajaxOptions, thrownError) {if(error_callback && typeof(error_callback) === "function") error_callback(xhr); })
    .complete(function(xhr) { if(showLoader) loader.hide('reload');});

}

var AlertMessage = Class.extend({

    init: function(message, addButton, timer) {

        if($('#alert').is(':visible')) this.hide();

        this.timer = timer ? timer * 1000 : null;
         $('#alert_message').html(message);
        this.intervalId = null;

        this.showLoader = true;


        if(addButton) $('#close_alert_message').show();
        else $('#close_alert_message').hide();

        this.is_visible = false;

    },

    show: function() {

    	if(this.showLoader) loader.show('message');
//    	else loader.hide('message');

    	if(this.is_visible && this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
            $('#alert').hide();
        }

        if(!$('#close_alert_message').is(':visible') && !this.timer) {
            $('#close_alert_message').unbind('click');
            $('#close_alert_message').click(this.hide.bind(this));
            $('#close_alert_message').show();
        }

        this.is_visible = true;
        $('#alert').css('top', $('#alert').outerHeight() * -1);
        $('#alert').show();

        $('#alert').animate({top: 0}, 400, function() {
            if(this.timer) this.intervalId = window.setInterval(this.didAppear.bind(this), this.timer);
        }.bind(this));

    },

    hide: function() {
        if(typeof(this.willHide) === "function") {
            this.willHide();
        }
        $('#alert').animate({top: $('#alert').outerHeight() * -1 + 'px'}, 400, this.didHide.bind(this));
    },

    didAppear: function() {

        this.hide();

        if(this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    },

    didHide: function() {

        $('#alert').hide();
        this.is_visible = false;

        if(typeof(this.afterHide) === "function") {
            this.afterHide();
        }

        if(this.showLoader) loader.hide('message');

    },

    addLoader: function(addLoader) {
    	if(addLoader) this.showLoader = true;
    	else this.showLoader = false;
    },

    addButton: function(addButton) {
    	if(addButton || addButton == 1) $('#close_alert_message').show();
    	else $('#close_alert_message').hide();

    },

    setTimer: function(timer) {
    	if(timer) timer *= 1000;
    	this.timer = timer;
    },

    isError: function(isError) {
        if(isError) $('#alert').removeClass('header').css('background-color', '#C41313');
        else $('#alert').removeAttr('style').addClass('header');
    },

    setMessage: function(message) {
         $('#alert_message').html(message);
    },

    isVisible: function() {
        return this.is_visible;
    },

    reset: function() {

    }

});

var message = new AlertMessage();

Object.getSize = function(object) {
    var size = 0;
    if(typeof object == 'object') {
        for(var key in object) {
            if (object.hasOwnProperty(key)) size++;
        }
    }
    return size;
}