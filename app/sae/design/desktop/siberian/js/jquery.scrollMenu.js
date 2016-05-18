/* 
 * Set fixed menu on top when page is scrolling
 * @author : GR
 * Markup :
 * <div id="action_bar" class="bg-blue15">
        <div id="wrapper_bar">
            <button type="submit">Cancel</button>
            <button type="submit">Save</button>
        </div>
    </div>
 */

(function($) {
    $.fn.scrollMenu = function() {
        var submit_btn = $(this).find('.submit_form');
        var left = $(this).find('#wrapper_bar').offset().left;
        var pos = $(this).offset();
        var clone = $(this).clone().appendTo('body').hide();
        clone.css({
            'top': '0px',
            'position': 'fixed',
            'width': '100%',
            'margin': '0',
            'padding': '0'
        });
        clone.find('#wrapper_bar').addClass('bg-blue15');
        clone.find('#wrapper_bar').css({
            'left': 567+'px',
            'width': '100%',
            'padding-left': (left+3)*2+'px',
            'position': 'relative'
        });
        $(window).scroll(function() {
            if($(this).scrollTop() > (pos.top + 10)) {
                clone.addClass('fixed').prependTo('form').show();
            } else if($(this).scrollTop() <= pos.top && clone.hasClass('fixed')) {
                clone.appendTo('body').hide();
            }
        });
    };
})(jQuery);


