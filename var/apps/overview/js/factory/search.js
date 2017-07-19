/*global
 App, device
 */

/**
 * Search
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("Search", function($pwaRequest) {

    var factory = {
        url     : null,
        agent   : null,
        extendedOptions: {}
    };

    factory.setAgent = function (agent, value_id) {
        agent.value_id = value_id;
        factory.agent = agent;

        /* The agent performs the finding of particular elements */
        factory.find = agent.find;
    };

    factory.findAll = function (parameters) {

        /* The url and agent must be non-null */
        if (!(this.url && this.agent)) {
            return $pwaRequest.reject("[Factory::Search.findAll] missing url and agent");
        }

        return $pwaRequest.post(factory.url, {
            urlParams: {
                value_id: parameters.value_id
            },
            data : parameters
        });
    };

    return factory;
});