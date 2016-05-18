App.directive('sbConnection', function() {
    return {
        restrict: 'E',
        scope: {
            has_connection: '=hasConnection'
        },
        template:
            '<div class="has-connection">' +
                '<div class="no_connection">{{ "You have gone offline" | translate }}</div>' +
            '</div>',
        replace: true
    };
});