/**
 * Folder rev 2
 *
 * @author Xtraball SAS
 */
angular.module('starter').factory('Folder2', function ($pwaRequest) {
    var factory = {
        value_id: null,
        folder_id: null,
        categories: [],
        showSearch: false,
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

    factory.findAll = function (valueId) {
        var localValueId = (valueId === undefined) ?
            this.value_id : valueId;

        if (!localValueId) {
            return $pwaRequest.reject('[Factory::Folder2.findAll] missing value_id');
        }

        var payload = $pwaRequest.getPayloadForValueId(factory.value_id);
        if (payload !== false) {
            factory.collection = angular.copy(payload.collection);
            factory.showSearch = angular.copy(payload.showSearch);

            return $pwaRequest.resolve();
        }

        // Otherwise fallback on PWA!
        $pwaRequest
            .get('folder2/mobile_list/findall', {
                urlParams: {
                    value_id: localValueId
                }
            })
            .then(function (result) {
                if (result.folders) {
                    factory.collection = result.collection;
                    factory.showSearch = result.showSearch;
                    return $pwaRequest.resolve();
                }
                return $pwaRequest.reject();
            });

        return $pwaRequest.reject();
    };

    /**
     * Fetch folder for given categoryId
     *
     * @param parentId
     */
    factory.fetchForParentId = function (parentId) {
        console.log('Folder2 fetchForParentId', parentId);
        var currentFolder;
        if (parentId === null) {
            console.log('Folder2 nullcase');
            currentFolder = _.find(factory.collection, {
                parent_id: null
            });
        } else {
            console.log('Folder2 notnullcase');
            currentFolder = _.find(factory.collection, {
                category_id: parseInt(parentId, 10)
            });
        }

        console.log('Folder2 currentFolder', currentFolder);

        return {
            folder: currentFolder,
            subfolders: _.filter(factory.collection, {
                parent_id: parseInt(currentFolder.category_id, 10)
            })
        };
    };

    return factory;
});
