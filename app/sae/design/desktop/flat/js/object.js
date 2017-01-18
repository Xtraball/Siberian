/* Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
// Inspired by base2 and Prototype
(function(){
    var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;

    // The base Class implementation (does nothing)
    this.Class = function(){};

    // Create a new Class that inherits from this class
    Class.extend = function(prop) {
        var _super = this.prototype;

        // Instantiate a base class (but only create the instance,
        // don't run the init constructor)
        initializing = true;
        var prototype = new this();
        initializing = false;

        // Copy the properties over onto the new prototype
        for (var name in prop) {
            // Check if we're overwriting an existing function
            prototype[name] = typeof prop[name] == "function" &&
            typeof _super[name] == "function" && fnTest.test(prop[name]) ?
            (function(name, fn){
                return function() {
                    var tmp = this._super;

                    // Add a new ._super() method that is the same method
                    // but on the super-class
                    this._super = _super[name];

                    // The method only need to be bound temporarily, so we
                    // remove it when we're done executing
                    var ret = fn.apply(this, arguments);
                    this._super = tmp;

                    return ret;
                };
            })(name, prop[name]) :
            prop[name];
        }

        // The dummy class constructor
        function Class() {
            // All construction is actually done in the init method
            if ( !initializing && this.init )
                this.init.apply(this, arguments);
        }

        // Populate our constructed prototype object
        Class.prototype = prototype;

        // Enforce the constructor to be what we expect
        Class.prototype.constructor = Class;

        // And make this class extendable
        Class.extend = arguments.callee;

        return Class;
    };
})();


var Modal = Class.extend({
    callbacks: null,
    id: null,
    url: null,
    dom: null,
    height: null,
    width: 1000,
    is_retain: true,
    is_loaded: false,
    do_resize: true,
    init: function(datas) {

        this.id = datas.id;
        var default_options = {
            callbacks: new Array(),
            dom: {
                container: $('#modal_'+this.id),
                content: $('#modal_content_'+this.id),
                details: $('#modal_details_'+this.id),
                close_elements: $('.close_modal_'+this.id)
            }
        };
        $.extend(this, default_options, datas);

    },

    show: function() {
        if(!this.is_loaded) {
            this.load();
        }
        else {
            this.fireCallback('willappear');
            $(document).keyup(function(e) {
                if(e.which == 27) this.hide();
            }.bind(this));

            this.dom.container.fadeIn(300);

            if(this.do_resize) {
                this.dom.content.css({"max-width": this.width});
                if(this.height) {
                    this.dom.content.css({height: this.height});
                }
                else {
                    $(window).resize(function() {
                        this.dom.content.css('height', $(window).outerHeight() - 75);
                        this.dom.details.css('height', $(window).outerHeight() - 180);
                    }.bind(this));
                    this.dom.content.css('height', $(window).outerHeight() - 75);
                    this.dom.details.css('height', $(window).outerHeight() - 180);
                }
            }

        }
    },

    hide: function() {
        this.fireCallback('willdisappear');
        $(window).unbind('resize');
        $(document).unbind("keyup");
        this.dom.container.fadeOut(300);
        setTimeout(function() {
            if(!this.is_retain) {
                this.destroy();
            }
        }.bind(this), 500);
    },

    load: function() {

        this.fireCallback('willload');
        loader.show();
        reload(null, this.url, false, function(datas) {

            var html = datas.modal_html;
            html = html.replaceAll('#{id}', this.id);
            $('body').append(html);

            for(var i in this.dom) {
                this.dom[i] = $(this.dom[i].selector);
            }

            this.dom.close_elements.click(function() {
                this.hide();
            }.bind(this));

            if(!this.height && this.do_resize) {
                this.dom.content.css('height', $(window).outerHeight() - 75);
                this.dom.details.css('height', $(window).outerHeight() - 180);
            }

            this.is_loaded = true;

            this.show();
            loader.hide();
            this.fireCallback('didload');

        }.bind(this), loader.hide.bind(loader));
    },

    destroy: function() {
        this.fireCallback('willdestroy');
        $(window).unbind('resize');
        this.dom.close_elements.unbind('click');
        this.dom.container.remove();
        this.is_loaded = false;

    },

    setCallback: function(id, callback) {
        this.callbacks[id] = callback;
        return this;
    },

    unsCallback: function(id) {
        this.callbacks[id] = null;
        return this;
    },

    getCallback: function(id) {

        var callback = function() {};

        if(typeof this.callbacks[id] == 'function') {
            callback = this.callbacks[id].bind(this);
        }

        return callback;
    },

    fireCallback: function(id) {
        this.getCallback(id).call(this);
        return this;
    }

});