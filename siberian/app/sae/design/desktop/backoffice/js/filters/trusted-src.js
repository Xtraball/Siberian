App.filter('trusted_src', function($sce) {
    return function(src) {
        return $sce.trustAsResourceUrl(src);
    };
});