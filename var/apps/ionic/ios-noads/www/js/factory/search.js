App.factory('Search', function ($rootScope, $sbhttp, Url) {

    var factory = {};

    /* Requires setting an url from which the results are fetched. */
    factory.url = null;

    /* Requires an agent, i.e. an actual factory (e.g. Places). */
    factory.agent = null;

    factory.setAgent = function (agent, value_id) {
        agent.value_id = value_id;
        factory.agent = agent;
        /* The agent performs the finding of particular elements */
        factory.find = agent.find;
    };

    factory.findAll = function (parameters) {

        /* The url and agent must be non-null */
        if (!(this.url && this.agent)) return;

        return $sbhttp({
            method: 'GET',
            url: Url.get(factory.url, {value_id: parameters.value_id}),
            params: parameters,
            responseType: 'json'
        });
    };

    return factory;
});