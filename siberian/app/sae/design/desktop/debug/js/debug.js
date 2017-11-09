/** Working with varnish js will help remove jquery dependency */
var obj = {
    get: function(mixed) {
        if(mixed.indexOf("#") == 0) {
            return this.getById(mixed.substr(1));
        } else if (mixed.indexOf(".") == 0) {
            return this.getByClassname(mixed.substr(1));
        } else {
            return this.getByTagName(mixed);
        }
    },
    getById: function(id) {
        return new Array(document.getElementById(id));
    },
    getByTagName: function(name) {
        return document.getElementsByTagName(name);
    },
    getByClassname: function(classname) {
        return document.getElementsByClassName(classname);
    },
    getIndex: function(els, index) {
        els = Array.isArray(els) ? els : new Array(els);
        if(els.length >= index+1) {
            return els[index];
        }
    },
    addClass: function(els, classname) {
        els = Array.isArray(els) ? els : new Array(els);
        for(index in els) {
            els[index].classList.add(classname);
        }
    },
    removeClass: function(els, classname) {
        els = Array.isArray(els) ? els : new Array(els);
        for(index in els) {
            els[index].classList.remove(classname);
        }
    },
    hasClass: function(els, classname) {
        els = Array.isArray(els) ? els : new Array(els);
        for(index in els) {
            els[index].classList.contains(classname);
        }
    }
};

var Debugger = {
    count_ajax: 0,
    debug_bar: obj.get("#debug-bar"),
    debug_tab: obj.get(".debug-tab"),
    debug_tab_link: obj.get(".debug-tab-link"),
    document_body: obj.get("body"),

    init: function() {
        obj.addClass(this.document_body, "debug-body");
        obj.removeClass(this.debug_bar, "debug-hide");
    },

    remove: function() {
        obj.removeClass(this.document_body, "debug-body");
        obj.addClass(this.debug_bar, "debug-hide");
    },

    toggleTab: function(el, classname) {
        if(classname == "__unset__") {
            /** Hide all */
            obj.addClass(this.debug_tab, "debug-hide");
            obj.removeClass(this.debug_tab_link, "debug-tab-active");

            return;
        }

        var elTab = obj.get("#"+classname);
        if(obj.hasClass(el, "debug-tab-active")) {
            obj.addClass(elTab, "debug-hide");
            obj.removeClass(el, "debug-tab-active");
        } else {
            /** Hide all */
            obj.addClass(this.debug_tab, "debug-hide");
            obj.removeClass(this.debug_tab_link, "debug-tab-active");

            /** Show selected */
            obj.removeClass(elTab, "debug-hide");
            obj.addClass(el, "debug-tab-active");
        }
    },

    updateAjax: function(debug) {
        var debug_lines = (countAjax != 0) ? "<hr />" : "";
        for(var i = 0; i < debug.length; i++) {
            debug_lines = "#"+countAjax+" "+ debug[i] + "<br />" + debug_lines;
            countAjax++;
        }

        var label = "PHP Ajax Errors (%count%) <i onclick='Debugger.clearAjax();' class='icon-trash'></i>".replace("%count%", countAjax);

        obj.get("#debug-tab-ajax").innerHtml(label);
        obj.get("#php-ajax-errors-pre").innerHTML(debug_lines + obj.get("#php-ajax-errors-pre").innerHTML());
    },

    clearAjax: function() {
        countAjax = 0;

        obj.get("#debug-tab-ajax").innerHtml("PHP Ajax Errors (0) <i onclick='Debugger.clearAjax();' class='icon-trash'></i>");
        obj.get("#php-ajax-errors-pre").innerHTML("");
    }
};

/** Overriding xmlHttpRequest */
var send = XMLHttpRequest.prototype.send;
XMLHttpRequest.prototype.send = function() {
    send.apply(this, Array.prototype.slice.call(arguments));

    var result = JSON.parse(this.responseText);
    if(this.responseText != "" && result && result.debug) {
        Debugger.updateAjax(result.debug);
    }
}