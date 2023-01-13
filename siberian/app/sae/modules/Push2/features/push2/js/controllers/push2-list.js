/**
 * Push v2
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 1.0.0
 */
angular
    .module('starter')
    .controller('Push2ListController', function ($scope, $filter, Customer, Push2, Push2Base, LinkService) {

        angular.extend(
            $scope,
            Push2Base, {

            });
    });
