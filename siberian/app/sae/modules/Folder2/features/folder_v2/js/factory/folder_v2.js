/**
 * Folder rev 2
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Folder2', function ($pwaRequest, Customer, Padlock) {
    var factory = {
        value_id: null,
        folder_id: null,
        categories: [],
        searchIndex: [],
        showSearch: false,
        allowLineReturn: false,
        extendedOptions: {}
    };

    /**
     *
     * @param valueId
     */
    factory.setValueId = function (valueId) {
        factory.value_id = valueId;
    };

    /**
     *
     * @param options
     */
    factory.setExtendedOptions = function (options) {
        factory.extendedOptions = options;
    };

    /**
     *
     * @param payload
     */
    factory.populate = function (payload) {
        factory.collection = payload.collection;
        factory.showSearch = payload.showSearch;
        factory.allowLineReturn = payload.allowLineReturn;
        factory.cardDesign = payload.cardDesign;
        factory.searchIndex = payload.searchIndex;

        factory.filterAccess();
    };

    factory.filterAccess = function () {
        var unlocked = Customer.can_access_locked_features || Padlock.unlocked_by_qrcode;

        var compute = function (collection) {
            var destination = [];
            angular.forEach(collection, function (folderItem) {
                if (!folderItem.is_active) {
                    return;
                }
                if (unlocked || !folderItem.is_locked || (folderItem.code === 'padlock')) {
                    if (unlocked && (folderItem.code === 'padlock')) {
                        return;
                    }

                    this.push(folderItem);
                }
            }, destination);
            return destination;
        };

        var computeIndex = function (collection) {
            var destination = [];
            angular.forEach(collection, function (folderItem) {
                if (!folderItem.feature.is_active) {
                    return;
                }
                if (unlocked || !folderItem.feature.is_locked || (folderItem.feature.code === 'padlock')) {
                    if (unlocked && (folderItem.feature.code === 'padlock')) {
                        return;
                    }

                    this.push(folderItem);
                }
            }, destination);
            return destination;
        };

        factory.collection = compute(angular.copy(factory.collection));
        factory.searchIndex = computeIndex(angular.copy(factory.searchIndex));
    };

    /**
     *
     * @param valueId
     */
    factory.findAll = function (valueId) {
        var localValueId = (valueId === undefined) ?
            this.value_id : valueId;

        if (!localValueId) {
            return $pwaRequest.reject('[Factory::Folder2.findAll] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            factory.populate(payload);

            return $pwaRequest.resolve();
        }

        // Otherwise fallback on PWA!
        return $pwaRequest
            .get('folder2/mobile_list/findall', {
                urlParams: {
                    value_id: localValueId
                }
            });
    };

    /**
     * Fetch folder for given categoryId
     *
     * @param parentId
     */
    factory.fetchForParentId = function (parentId) {
        var currentFolder;
        if (parentId === null) {
            currentFolder = _.find(factory.collection, {
                parent_id: null
            });
        } else {
            currentFolder = _.find(factory.collection, {
                category_id: parseInt(parentId, 10)
            });
        }

        return {
            folder: currentFolder,
            subfolders: _.filter(factory.collection, {
                parent_id: parseInt(currentFolder.category_id, 10)
            })
        };
    };

    return factory;
});
