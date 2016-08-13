function EventToDirective(app, directive_name, eventName) {
  return app.directive(directive_name, ['$parse', function($parse) {
    return {
        restrict: 'A',
        compile: function($element, attr) {
            return function(scope, element, attr) {
                element.on(eventName, function(event) {
                    scope.$apply(($parse(attr[directive_name])).bind(null, scope, {$event:event}));
                });
            };
        }
    };
  }]);
}

EventToDirective(App, 'sbError', 'error');
EventToDirective(App, 'sbLoad', 'load');
