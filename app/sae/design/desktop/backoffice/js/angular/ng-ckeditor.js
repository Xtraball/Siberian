/*
 * autor: Miller Augusto S. Martins
 * e-mail: miller.augusto@gmail.com
 * github: miamarti
 * */
(function(window, document) {
    "use strict";
    (angular.module('ng.ckeditor', [ 'ng' ])).directive('ngCkeditor', function() {

	CKEDITOR.on('instanceCreated', function(event) {
	    var editor = event.editor, element = editor.element;
	    if (element.getAttribute('class') == 'simpleEditor') {
            editor.on('configLoaded', function() {
                editor.config.removePlugins = 'colorbutton,find,flash,font, forms,iframe,image,newpage,removeformat, smiley,specialchar,stylescombo,templates';
                editor.removeButtons = 'About';
                editor.config.toolbarGroups = [ {
                name : 'editing',
                groups : [ 'basicstyles', 'links' ]
                }, {
                name : 'undo'
                }, {
                name : 'clipboard',
                groups : [ 'selection', 'clipboard' ]
                } ];
            });
	    }
	});

	var container = function(scope, element, attrs) {
	    element[0].innerHTML = '<div id="' + attrs.bind + '"></div> <div class="totalTypedCharacters"></div>';
	    var config = {
		    removeButtons : (attrs.removeButtons != undefined) ? 'About,' + attrs.removeButtons : 'About'
	    };
	    if (attrs.removePlugins != undefined) {
		    config.removePlugins = attrs.removePlugins;
	    }
	    if (attrs.skin != undefined) {
		    config.skin = attrs.skin;
	    }
	    //setTimeout(function() {
            var editor = CKEDITOR.appendTo(attrs.bind, config, '');
            (editor).on('change', function(evt) {
                eval('(function(){ scope.' + attrs.bind + ' = evt.editor.getData(); })()');
                if (attrs.msnCount != undefined) {
                    element[0].querySelector('.totalTypedCharacters').innerHTML = attrs.msnCount + " " + evt.editor.getData().length;
                }
            });
            scope.$watch(attrs.bind, function(value) {
                editor.setData(value);
            });
	    //}, 500);
	};
	return {
	    restrict : 'E',
	    link : container
	};
    });
})(window, document);
