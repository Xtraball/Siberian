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

});