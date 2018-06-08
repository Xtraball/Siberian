/**
 * Set Meal
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('SetMeal', function ($pwaRequest) {
    var factory = {
        value_id: null,
        set_meal_id: null,
        displayed_per_page: null,
        extendedOptions: {},
        collection: []
    };

    /**
     *
     * @param valueId
     */
    factory.setValueId = function (valueId) {
        // Clear collection when changing valueId!
        if (factory.value_id !== valueId) {
            factory.collection = [];
        }
        factory.value_id = valueId;
    };

    /**
     *
     * @param setMealId
     */
    factory.setSetMealId = function (setMealId) {
        factory.set_meal_id = setMealId;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     * Pre-Fetch feature.
     *
     * @param page
     */
    factory.preFetch = function (page) {
        factory.findAll();
    };

    /**
     *
     * @param offset
     * @param refresh
     */
    factory.findAll = function (offset, refresh) {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::SetMeal.findAll] missing value_id');
        }

        return $pwaRequest.get('catalog/mobile_setmeal_list/findall', angular.extend({
            urlParams: {
                value_id: this.value_id,
                offset: offset
            },
            refresh: refresh
        }, factory.extendedOptions));
    };

    /**
     * Fallback for direct call.
     *
     * @param setMealId
     */
    factory.find = function (setMealId) {
        var localSetMealId = setMealId;
        if (localSetMealId === undefined) {
            localSetMealId = factory.setMealId;
        }

        if (!this.value_id && !localSetMealId) {
            return $pwaRequest.reject('[Factory::SetMeal.find] missing value_id or set_meal_id');
        }

        return $pwaRequest.get('catalog/mobile_setmeal_view/find', {
            urlParams: {
                value_id: this.value_id,
                set_meal_id: localSetMealId
            }
        });
    };

    /**
     * Search for set meal payload inside cached collection
     *
     * @returns {*}
     */
    factory.getSetMeal = function () {
        if (!this.value_id) {
            return $pwaRequest.reject('[Factory::SetMeal.getSetMeal] missing value_id');
        }

        var set_meal = _.get(_.filter(factory.collection, function (filter_set_meal) {
            return (filter_set_meal.id == factory.set_meal_id);
        })[0], 'embed_payload', false);

        if (!set_meal) {
            // Well then fetch it!
            return factory.find();
        }
        return $pwaRequest.resolve(set_meal);
    };

    return factory;
});
