App.service('ContextualMenu', function($ionicSideMenuDelegate, $timeout, HomepageLayout) {
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
      get: function() { return ((angular.isFunction(_is_enabled_function) && _is_enabled_function) || (function() { return self.exists; }))(); }
    });

    Object.defineProperty(self, "direction", {
        get: function() {
            return HomepageLayout.properties.menu.position == 'right' ? 'left' : 'right';
        }
    });

    self.reset = function() {
        $timeout(function() {
            _exists = false;
            _is_enabled_function = (function() { return self.exists; });
            _width = null;
            _templateURL = null;
        });
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

    self.toggle = function(open) {
        var direction = self.direction.slice(0, 1).toUpperCase()+self.direction.slice(1);

        if(!(open === true || open === false)) {
            open = !$ionicSideMenuDelegate["isOpen"+direction]();
        }

        if(self.exists && self.isEnabled) {
            $ionicSideMenuDelegate["toggle"+direction](open);
        }
    }

    self.open = function() {
        self.toggle(true);
    };

    self.close = function() {
        self.toggle(false);
    };


    return self;
});
