App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/translation/backoffice_list", {
        controller: 'TranslationController',
        templateUrl: BASE_URL+"/translation/backoffice_list/template",
        code: "list"
    }).when(BASE_URL+"/translation/backoffice_edit", {
        controller: 'TranslationEditController',
        templateUrl: BASE_URL+"/translation/backoffice_edit/template",
        code: "edit"
    }).when(BASE_URL+"/translation/backoffice_edit/langId/:lang_id", {
        controller: 'TranslationEditController',
        templateUrl: BASE_URL+"/translation/backoffice_edit/template",
        code: "edit"
    });

}).controller("TranslationController", function($scope, $location, Header, Label, SectionButton, Translations) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form_loader_is_visible = false;
    Translations.type = $scope.code;

    $scope.button = new SectionButton(function() {
        $location.path("translation/backoffice_edit");
    });

    Translations.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Translations.findAll().success(function(data) {
        $scope.languages = data;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.toggleLanguage = function(language) {
        language.saving = true;
        Translations.toggle(language.code, !language.enabled).success(function(data) {
            if (angular.isObject(data) && data.success) {
                language.enabled = !language.enabled;
                $scope.message.setText(data.message).isError(false).show();
            } else {
                $scope.message.setText(angular.isObject(data) && data.message ? data.message : Label.save.error)
                    .isError(true).show();
            }
        }).error(function(data) {
            $scope.message.setText(angular.isObject(data) && data.message ? data.message : Label.save.error)
                .isError(true).show();
        }).finally(function() {
            language.saving = false;
        });
    };

    $scope.packs = [];
    $scope.backups = [];

    $scope.loadPacks = function() {
        Translations.packs().success(function(data) {
            $scope.packs = data.packs || [];
            $scope.backups = data.backups || [];
        });
    };
    $scope.loadPacks();

    $scope.stalePacks = function() {
        return $scope.packs.filter(function(pack) {
            return pack.stale;
        });
    };

    $scope.confirmed = function(text) {
        return window.confirm(text);
    };

    $scope.formatSize = function(bytes) {
        return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + " MB" : Math.max(1, Math.round(bytes / 1024)) + " KB";
    };

    $scope.packCall = function(item, request, done) {
        item.busy = true;
        request.success(function(data) {
            var ok = angular.isObject(data) && data.success;
            $scope.message.setText(angular.isObject(data) && data.message ? data.message : Label.save.error)
                .isError(!ok).show();
            done(data, ok);
        }).error(function(data) {
            $scope.message.setText(angular.isObject(data) && data.message ? data.message : Label.save.error)
                .isError(true).show();
        }).finally(function() {
            item.busy = false;
        });
    };

    $scope.comparePack = function(pack) {
        $scope.packCall(pack, Translations.comparePack(pack.code), function(data, ok) {
            if (ok) {
                pack.report = data.message + " (" + data.only_in_pack + " / " + data.pack + ")";
            }
        });
    };

    $scope.switchPack = function(pack, mode) {
        $scope.packCall(pack, Translations.switchPack(pack.code, mode), function(data, ok) {
            if (ok) {
                $scope.loadPacks();
                Translations.findAll().success(function(languages) {
                    $scope.languages = languages;
                });
            }
        });
    };

    $scope.restoreBackup = function(backup) {
        $scope.packCall(backup, Translations.restoreBackup(backup.id), function(data, ok) {
            if (ok) {
                $scope.loadPacks();
                Translations.findAll().success(function(languages) {
                    $scope.languages = languages;
                });
            }
        });
    };

    $scope.deleteBackup = function(backup) {
        $scope.packCall(backup, Translations.deleteBackup(backup.id), function(data, ok) {
            if (ok) {
                $scope.loadPacks();
            }
        });
    };

}).controller("TranslationEditController", function($http, $rootScope, $scope, $location, $routeParams, $queue, Header, Label,
                                                    SectionButton, $timeout, Translations, Url) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;
    $scope.form_loader_is_visible = false;
    $scope.translation = {};
    $scope.can_translate = false;
    Translations.type = $scope.code;

    $scope.yandexTranslation = {
        showProgress: false,
        progress: 0
    };

    $scope.filter = {
        search: "",
        isStrict: false,
        isCaseSensitive: false,
        useComparator: function() {
            return $scope.filter.isStrict || $scope.filter.isCaseSensitive;
        },
        comparator: function(actual, expected) {
            if (!$scope.filter.isCaseSensitive) {
                expected = actual.toLowerCase();
            }

            if (!$scope.filter.isStrict) {
                return actual.indexOf(expected) >= 0;
            }
            return angular.equals(actual, expected);
        }
    };

    $scope.currentClass = [];

    $scope.updateClass = function(key) {
        $scope.currentClass[key] = "highlight";
    };

    Translations.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Translations
        .find($routeParams.lang_id)
        .success(function(data) {

            $scope.translation.country_code = data.country_code;
            $scope.languages = data;
            $scope.section_title = data.section_title;
            $scope.countries = data.country_codes;
            $scope.search_context = data.search_context;
            $scope.translation_files = data.translation_files;
            $scope.translation_files_data = data.translations;
            $scope.is_edit = data.is_edit;

            console.log($scope.search_context);

            if ($scope.translation.country_code) {
                $scope.can_translate = ($scope.available_target.indexOf($scope.translation.country_code.split("_")[0]) != -1);
            } else {
                $scope.can_translate = false;
            }
        }).finally(function() {
            $scope.content_loader_is_visible = false;
        });

    $scope.changeCountry = function() {
        $scope.can_translate = ($scope.available_target.indexOf($scope.translation.country_code.split("_")[0]) != -1);
    };

    $scope.getRowColor = function (item) {
        if (item.user === null) {
            return (item.default === item.original || item.default === null) ? "warning" : "info";
        }
        return "success";
    };

    $scope.selectFile = function() {
        $scope.resize();
        $scope.translation.collection = $scope.translation_files_data[$scope.translation.file];
    };

    $scope.notSorted = function(obj){
        if (!obj) {
            return [];
        }
        return Object.keys(obj);
    };

    $scope.openFile = function(file) {
        $scope.translation.file = file;
        $scope.selectFile();
    };
    
    $scope.save = function() {

        $scope.form_loader_is_visible = true;

        Translations.save($scope.translation).success(function(data) {

            $scope.is_edit = true;

            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
                $scope.message.isError(false);
            } else {
                $scope.message.isError(true);
            }
            $scope.message.setText(message)
                .show()
            ;

        }).error(function(data) {
            var message = Label.save.error;
            if(angular.isObject(data) && angular.isDefined(data.message)) {
                message = data.message;
            }
    
            $scope.message.setText(message)
                .isError(true)
                .show()
            ;
        }).finally(function() {
            $scope.form_loader_is_visible = false;
        });
    };

    $scope.available_target = [
        "be", "ca", "cs", "da", "de", "el", "es", "et",
        "fi", "fr", "hu", "it", "lt", "lv", "mk", "nl",
        "no", "pt", "ru", "sk", "sl", "sq", "sv", "tr",
        "uk"
    ];

    $scope.translate = function(key, target) {
        return $http({
            method: 'POST',
            url: Url.get("translation/backoffice_edit/translate"),
            data: {"text": key, "target": target},
            cache: false,
            responseType:'json'
        });
    };

    $scope.translateAll = function() {

        var size = 0;
        var remain = 0;
        var callbackTranslate = function (key) {
            if(!breakOnError) {
                $scope.translate(key, $scope.translation.country_code.split("_")[0])
                    .then(function(response) {
                        if (response.data && response.data.error) {
                            $scope.message.setText(response.data.message).isError(true).show();
                            breakOnError = true;
                            return;
                        }
                        if(response.data && response.data.result && response.data.result.text) {
                            $scope.translation.collection[key].user = response.data.result.text[0];
                        }
                        $scope.updateClass(key);
                        $scope.yandexTranslation.progress = Math.round((size - remain) / size * 100);
                        remain = remain - 1;
                    }, function (response) {
                        if(response.data && response.data.message) {
                            $scope.message.setText(response.data.message)
                                .isError(true)
                                .show()
                            ;
                        }
                        breakOnError = true;
                    });
            } else {
                $scope.yandexTranslation.progress = 0;
                $scope.yandexTranslation.showProgress = false;
            }
        };

        var breakOnError = false,
            translateQueue = $queue.queue(callbackTranslate, {
                delay: 100,
                paused: true,
                complete: function() {
                    $scope.yandexTranslation.showProgress = false;
                }
            });

        angular.forEach($scope.translation.collection, function(value, key) {
            translateQueue.add(key);
        });

        size = translateQueue.size();
        remain = size;

        if (size > 0) {
            translateQueue.start();
            $scope.yandexTranslation.showProgress = true;
        }
    };

    $scope.translateMissing = function() {

        var size = 0;
        var remain = 0;
        var callbackTranslate = function (key) {
            if(!breakOnError) {
                $scope
                    .translate(key, $scope.translation.country_code.split("_")[0])
                    .then(function(response) {
                        if (response.data && response.data.error) {
                            $scope.message.setText(response.data.message).isError(true).show();
                            breakOnError = true;
                            return;
                        }
                        if(response.data && response.data.result && response.data.result.text) {
                            $scope.translation.collection[key].user = response.data.result.text[0];
                        }
                        $scope.updateClass(key);
                        $scope.yandexTranslation.progress = Math.round((size - remain) / size * 100);
                        remain = remain - 1;
                    }, function (response) {
                        if(response.data && response.data.message) {
                            $scope.message.setText(response.data.message)
                                .isError(true)
                                .show()
                            ;
                        }
                        breakOnError = true;
                    });
            } else {
                $scope.yandexTranslation.progress = 0;
                $scope.yandexTranslation.showProgress = false;
            }
        };

        var breakOnError = false,
            translateQueue = $queue.queue(callbackTranslate, {
                delay: 100,
                paused: true,
                complete: function() {
                    $scope.yandexTranslation.showProgress = false;
                }
            });

        angular.forEach($scope.translation.collection, function(value, key) {
            if ((value.user === null || value.user === "") &&
                (value.default === value.original || value.default === null)) {
                translateQueue.add(key);
            }
        });

        size = translateQueue.size();
        remain = size;

        if (size > 0) {
            translateQueue.start();
            $scope.yandexTranslation.showProgress = true;
        }
    };

    $scope.resize = function () {
        document.querySelector('.wrapper.inner_content').style.width = "calc(100% - 80px)";
    };

    $timeout(function () {
        $scope.resize();
    }, 1000);
});

App.filter('multiWordsFilter', function($filter) {
    return function(inputArray, searchTerms, isStrict, isCaseSensitive) {
        let result;
        if (!isStrict) {
            searchTerms = (searchTerms || '').split(/\s+/);
        } else {
            searchTerms = [searchTerms];
        }

        result = [];
        searchTerms.forEach(function(searchTerm) {
            if (!isCaseSensitive) {
                result = result.concat($filter('filter')(inputArray, searchTerm));
            } else {
                result = result.concat($filter('filter')(inputArray, searchTerm, function (actual, expected) {
                    return actual.indexOf(expected) >= 0;
                }));
            }
        });

        if (result.length > 0) {
            result = $filter('repeatUnique')(result, 'search');
        }
        return result;
    };
}).filter('repeatUnique', function() {
    // we will return a function which will take in a collection
    // and a keyname
    return function(collection, keyName) {
        // we define our output and keys array;
        var output = [],
            keys = [];

        // we utilize angular's foreach function
        // this takes in our original collection and an iterator function
        angular.forEach(collection, function(item) {
            if (item) {
                // we check to see whether our object exists
                var key = item[keyName];
                // if it's not already part of our keys array
                if(keys.indexOf(key) === -1) {
                    // add it to our keys array
                    keys.push(key);
                    // push this item to our final output array
                    output.push(item);
                }
            }
        });
        // return our array which should be devoid of
        // any duplicates
        return output;
    };
});
