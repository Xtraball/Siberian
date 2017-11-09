
App.filter('translate', function (Translator) {

    return function (text) {
        return Translator.get(text);
    };

});
