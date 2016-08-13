App.service('ContextualMenu', function() {
    var DEFAULT_WIDTH = 275;

    var self = {};

    var _exists, _templateURL, _width, _is_enabled_function;

    Object.defineProperty(self, "exists", {
      get: function() { return _exists; }
    });

    Object.defineProperty(self, "templateURL", {
      get: function() { return _templateURL; }
    });

    Object.defineProperty(self, "width", {
      get: function() { return (angular.isNumber(_width) && _width > 0 && _width) || DEFAULT_WIDTH; }
    });

    Object.defineProperty(self, "isEnabled", {
      get: function() { return ((angular.isFunction(_is_enabled_function) && _is_enabled_function) || (function() { return true; }))(); }
    });

    self.reset = function() {
      _exists = false;
      _width = null;
      _templateURL = null;
      _is_enabled_function = (function() { return true; });
    };
    self.reset();

    self.set = function(templateURL, width, is_enabled_function) {
      if(angular.isString(templateURL) && templateURL.length > 0) {
        _exists = true;
        _templateURL = templateURL;
        _width = width;
        _is_enabled_function = is_enabled_function;
      } else {
        self.reset();
      }

      return (function() {
        if(_templateURL == templateURL) {
          self.reset();
        }
      });
    };

    return self;
});
