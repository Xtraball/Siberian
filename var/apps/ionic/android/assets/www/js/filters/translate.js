
App.filter('translate', function ($translate) {

    return function (text) {
        return $translate.instant(text);
    };

});
