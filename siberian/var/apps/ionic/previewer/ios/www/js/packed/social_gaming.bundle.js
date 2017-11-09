/*global
 App, angular, BASE_PATH
 */

angular.module("starter").controller("SocialgamingViewController", function($scope, $stateParams, SocialGaming) {

    angular.extend($scope, {
        is_loading  : true,
        value_id    : $stateParams.value_id,
        factory     : SocialGaming,
        collection  : [],
        card_design : false
    });

    SocialGaming.setValueId($stateParams.value_id);

    $scope.loadContent = function() {
        SocialGaming.findAll()
            .then(function(data) {
                $scope.game             = data.game;
                $scope.team_leader      = data.team_leader;
                $scope.collection       = data.collection;
                $scope.icon_url         = data.icon_url;
                $scope.page_title       = data.page_title;
                $scope.is_loading       = false;
            });
    };

    $scope.loadContent();

});;/*global
    App, device, angular
 */

/**
 * SocialGaming
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("SocialGaming", function($rootScope, $pwaRequest, SB) {

    var factory = {
        value_id        : null,
        extendedOptions : {}
    };

    /**
     *
     * @param value_id
     */
    factory.setValueId = function(value_id) {
        factory.value_id = value_id;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function(options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     *
     * @param page
     */
    factory.preFetch = function(page) {
        factory.findAll();
    };


    factory.findAll = function(offset) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::SocialGaming.findAll] missing value_id");
        }

        return $pwaRequest.get("socialgaming/mobile_view/findall", angular.extend({
            urlParams: {
                value_id    : this.value_id,
                offset      : offset
            }
        }, factory.extendedOptions));
    };

    $rootScope.$on(SB.EVENTS.CACHE.clearSocialGaming, function() {

        $pwaRequest.cache("socialgaming/mobile_view/findall", {
            urlParams: {
                value_id : factory.value_id
            }
        });
    });

    return factory;
});
