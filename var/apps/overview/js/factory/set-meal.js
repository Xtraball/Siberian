/*global
 App, device, angular
 */

/**
 * SetMeal
 *
 * @author Xtraball SAS
 */
angular.module("starter").factory("SetMeal", function($pwaRequest) {

    var factory = {
        value_id            : null,
        set_meal_id         : null,
        displayed_per_page  : null,
        extendedOptions     : {},
        collection          :[]
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
     * @param value_id
     */
    factory.setSetMealId = function(set_meal_id) {
        factory.set_meal_id = set_meal_id;
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

    factory.findAll = function(offset, refresh) {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::SetMeal.findAll] missing value_id");
        }

        return $pwaRequest.get("catalog/mobile_setmeal_list/findall", angular.extend({
            urlParams: {
                value_id    : this.value_id,
                offset      : offset
            },
            refresh: refresh
        }, factory.extendedOptions));
    };

    /**
     * Fallback for direct call.
     *
     * @param set_meal_id
     */
    factory.find = function(set_meal_id) {

        if(set_meal_id === undefined) {
            set_meal_id = factory.set_meal_id;
        }

        if(!this.value_id && !set_meal_id) {
            return $pwaRequest.reject("[Factory::SetMeal.find] missing value_id or set_meal_id");
        }

        return $pwaRequest.get("catalog/mobile_setmeal_view/find", {
            urlParams: {
                value_id        : this.value_id,
                set_meal_id     : set_meal_id
            }
        });
    };

    /**
     * Search for set meal payload inside cached collection
     *
     * @param set_meal_id
     * @returns {*}
     */
    factory.getSetMeal = function() {

        if(!this.value_id) {
            return $pwaRequest.reject("[Factory::SetMeal.getSetMeal] missing value_id");
        }

        var set_meal = _.get(_.filter(factory.collection, function(set_meal) {
            return (set_meal.id == factory.set_meal_id);
        })[0], "embed_payload", false);

        if(!set_meal) {
            /** Well then fetch it. */
            return factory.find();

        } else {

            return $pwaRequest.resolve(set_meal);
        }
    };

    return factory;
});
