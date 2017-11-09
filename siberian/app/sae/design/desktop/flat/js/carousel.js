
/*global window, jQuery */
var Carousel = Class.extend({
    container: null,
    slide_width: 0,
    is_responsive: false,
    is_locked: false,
    is_dragging_feature: false,
    init: function(element, options) {
        this.fixed_item_width = 98;
        this.container = element;
        if(options.responsive_parent) {
            this.container_base_width = options.responsive_parent.width()-32;
            this.calculatedNbItems = Math.floor(this.container_base_width/this.fixed_item_width);
            options.responsive_parent.css({"margin":"auto"});
            this.is_responsive = true;
        } else {
            this.container_base_width = this.container.width();
            this.calculatedNbItems = 6;
        }

        var data_max_icons = parseInt(this.container.attr('data-max-icons'), 10);

        this.options = {
            items           : this.container.children('li:visible'),
            item_width      : this.container.children('li:first').outerWidth(true),
            items_per_page  : this.calculatedNbItems,
            current_page    : 1,
            next_button     : null,
            prev_button     : null,
            pager           : $('#'+this.container.attr('data-pager')),
            max_icons_in_app: (data_max_icons >= 0 ? data_max_icons : Infinity),
            next_button_interval_id : null,
            prev_button_interval_id : null,
            is_folder: false
        };

        this.options = $.extend({}, this.options, options || {});
        if(this.options.pager) this.options.pager = $(this.options.pager);
        this.container.css({position: 'relative', transform: "translateX(0)"});

        this.update();
        this.processEvents();

    },
    processEvents: function() {
        if(this.options.next_button) {
            this.options.next_button.click(function() {
                this.slideToTheNextPage();
            }.bind(this));
        }
        if(this.options.prev_button) {
            this.options.prev_button.click(function() {
                this.slideToThePreviousPage();
            }.bind(this));
        }

        if(this.options.pager) {
            if(this.options.pager.children().length > 1) {
                this.options.pager.children().each(function(i, e) {
                    if(i == 0) $(e).addClass('selected');
                    $(e).click(function() {
                        if(!$(e).hasClass('selected')) {
                            this.options.pager.children().removeClass('selected');
                            $(e).addClass('selected');
                            this.slideToPage(i+1);
                        }
                        return false;
                    }.bind(this));
                }.bind(this));
            }
            else {
                this.options.pager.children().hide();
            }
        }

        return this;
    },
    destroy: function() {
        this.options.prev_button.unbind('click');
        this.options.next_button.unbind('click');
        return this;
    },
    update: function() {
        this.options.items = this.container.children('li:visible');

        if(this.is_responsive) {
            this.options.responsive_parent.css('width', '85%');
            this.container_base_width = this.options.responsive_parent.width()-32;
            this.options.items_per_page = Math.floor(this.container_base_width/this.fixed_item_width);
        }

        var nbr_of_icons_in_carousel = 0;
        this.options.items.each(function() {
            if(this.id != 'option_value_more_items') nbr_of_icons_in_carousel++;
        });

        if((nbr_of_icons_in_carousel > this.options.max_icons_in_app) && this.options.is_folder == false) {
            $('#option_value_more_items').show();
            this.options.items = this.container.children('li:visible');
        }
        else $('#option_value_more_items').hide();

        this.slide_width = this.options.item_width*this.options.items_per_page;

        this.container.css('width', this.slide_width*(this.getNumberOfPages()+1));
        this.container.parent().css('width', this.slide_width);

        if(this.getCurrentPage() > this.getNumberOfPages()) this.slideToThePreviousPage();
        else this.checkButtons();

        return this;
    },
    reset: function() {
        this.container.css('left', 0);
        if(this.options.pager) {
            this.options.pager.children().removeClass('selected');
            this.options.pager.children(':first').addClass('selected');
        }
    },
    slideTo: function(dir) {

        var next_page = this.getCurrentPage();
        if(dir == 'left') next_page--;
        else if(dir == 'right') next_page++;
        else if(dir == 'last') next_page = this.getNumberOfPages();

        this.slideToPage(next_page);

        return this;
    },

    slideToPage: function(nbr) {

        if(this.is_locked || nbr > this.getNumberOfPages()) return;

        this.is_locked = true;
        nbr--;

        var left = this.slide_width * nbr * -1;
        left = Math.min(left, 0);

        this.container.css("transform", "translateX("+left+"px)");
        window.setTimeout(function() {
            this.checkButtons();
            this.is_locked = false;
            $("#option_values").sortable("refresh");
        }.bind(this), 450);

        return this;
    },

    slideToThePreviousPage: function() {
        if(this.canGoToThePreviousPage()) {
            this.slideTo('left');
        }

        return this;
    },

    slideToTheNextPage: function() {
        if (this.canGoToTheNextPage()) {
            this.slideTo('right');
        }

        return this;
    },

    slideToTheFirstPage: function() {
        if (this.canGoToThePreviousPage()) {
            this.slideToPage(1);
        }
    },

    slideToTheLastPage: function() {
        if (this.canGoToTheNextPage()) {
            this.slideToPage(this.getNumberOfPages());
        }
    },

    slideToItem: function(item) {
        var pos = 0;
        item.parent().children(item.get(0).nodeName.toLowerCase()+':visible').each(function() {
            if($(this).get(0) == item.get(0)) return false;
            else pos++;
        });
        if(pos) {
            var page = Math.ceil((pos + 1) / this.options.items_per_page);
            this.slideToPage(page);
        }
    },

    canGoToThePreviousPage: function() {
        return this.getCurrentPage() > 1 ? true : false;
    },

    canGoToTheNextPage: function() {
        return this.getCurrentPage() + 1 <= this.getNumberOfPages();
    },

    getCurrentPage: function() {
        var matrix = this.container.css("transform").split('(')[1].split(')')[0].split(',');
        var current_left = matrix.length == 6 ? parseInt(matrix[4]) * -1 : 0;
        var page_width = this.slide_width;
        return Math.ceil(current_left / page_width) + 1;
    },

    getNumberOfPages: function() {
        return Math.max(1, Math.ceil(this.options.items.length/this.options.items_per_page));
    },

    checkButtons: function() {

        if(this.options.prev_button) {
            if(this.canGoToThePreviousPage()) this.options.prev_button.removeClass('active');
            else this.options.prev_button.addClass('active');
        }
        if(this.options.next_button) {
            if(this.canGoToTheNextPage()) this.options.next_button.removeClass('active');
            else this.options.next_button.addClass('active');
        }

        return this;
    },

    getPrevButtonPosition: function() {
        if(!this.prev_button_position) {
            this.prev_button_position = {
                x1: this.options.prev_button.offset().left,
                y1: this.options.prev_button.offset().top,
                x2: this.options.prev_button.offset().left + this.options.prev_button.outerWidth(true),
                y2: this.options.prev_button.offset().top + this.options.prev_button.outerHeight(true)
            };
        }

        return this.prev_button_position;
    },

    getNextButtonPosition: function() {

        if(!this.next_button_position) {
            this.next_button_position = {
                x1: this.options.next_button.offset().left,
                y1: this.options.next_button.offset().top,
                x2: this.options.next_button.offset().left + this.options.next_button.outerWidth(true),
                y2: this.options.next_button.offset().top + this.options.next_button.outerHeight(true)
            };
        }

        return this.next_button_position;
    },

    startInterval: function(dir) {
        if(dir == 'next' && !this.options.next_button_interval_id) this.options.next_button_interval_id = setInterval(this.slideToTheNextPage.bind(this), 100);
        else if(dir == 'prev' && !this.options.prev_button_interval_id) this.options.prev_button_interval_id = setInterval(this.slideToThePreviousPage.bind(this), 100);

        return this;
    },

    stopInterval: function(dir) {
        if(dir == 'next' && this.options.next_button_interval_id) {
            clearInterval(this.options.next_button_interval_id)
            this.options.next_button_interval_id = null;
        }
        else if(dir == 'prev' && this.options.prev_button_interval_id) {
            clearInterval(this.options.prev_button_interval_id)
            this.options.prev_button_interval_id = null;
        }

        return this;
    }

});

$.fn.sbCarousel = function(o) {

    return this.each(function() {
        var singleton = $(this).data('carousel');
        if (singleton) {
            if (o) {
                $.extend(singleton.options, o);
                singleton.reset();
            }
        } else {
            $(this).data('carousel', new Carousel($(this), o));
        }
    });

};
