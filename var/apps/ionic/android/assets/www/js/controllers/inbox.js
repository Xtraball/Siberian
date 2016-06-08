App.config(function($stateProvider) {

    $stateProvider.state('inbox-list', {
        url: BASE_PATH+'/inbox/mobile_list/index/value_id/:value_id',
        templateUrl: 'templates/inbox/l1/list.html',
        controller: 'InboxListController'
    }).state('inbox-view', {
        url: BASE_PATH+'/inbox/mobile_view/index/message_id/:message_id/value_id/:value_id',
        templateUrl: 'templates/cms/page/l1/view.html',
        controller: 'InboxViewController'
    }).state('inbox-comment-view', {
        url: BASE_PATH+'/inbox/mobile/comment_view/index/message_id/:message_id/value_id/:value_id',
        templateUrl: 'templates/inbox/l1/comment/view.html',
        controller: 'InboxCommentViewController'
    }).state('inbox-comment-post', {
        url: BASE_PATH+'/inbox/mobile/comment_post/index/message_id/:message_id/value_id/:value_id',
        templateUrl: 'templates/inbox/l1/comment/post.html',
        controller: 'InboxCommentPostController'
    })

}).controller('InboxListController', function($ionicModal, $rootScope, $scope, $state, $stateParams, Customer, Inbox, AUTH_EVENTS) {

    $scope.value_id = Inbox.value_id = $stateParams.value_id;

    $scope.factory = Inbox;
    $scope.collection = new Array();

    $scope.can_load_older_posts = true;
    $scope.is_logged_in = Customer.isLoggedIn();

    $scope.module_code = "inbox";

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.$on(AUTH_EVENTS.loginSuccess, function() {
        $scope.is_logged_in = true;
        $scope.loadContent();
    });

    $scope.$on(AUTH_EVENTS.logoutSuccess, function() {
        $scope.is_logged_in = false;
        $scope.loadContent();
    });


    $scope.loadContent = function() {
        $scope.is_loading = true;
        if(Customer.isLoggedIn()) {
            $scope.customer_id  = Customer.id;
        } else {
            $scope.customer_id  = null;
        }

        Inbox.findAll($scope.customer_id, $scope.collection.length).success(function (data) {
            $scope.page_title = data.page_title;
            $scope.collection = $scope.collection.concat(data.collection);

            $scope.can_load_older_posts = !!data.collection.length;

        }).finally(function() {
            $scope.is_loading = false;
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });
    };

    $scope.showItem = function(item) {
        $state.go("inbox-view", {message_id: item.id, value_id: $scope.value_id});
    };


    $scope.login = function($scope) { 
        $rootScope.loginFeature = true; 
        Customer.loginModal($scope) ;
    };

    $scope.loadContent();

}).controller('InboxViewController', function($ionicHistory, $ionicPopup, $scope, $state, $stateParams, Inbox) {

    $scope.module_code = "inbox";

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.template_header = "templates/inbox/l1/view/subheader.html";
    $scope.template_tabs = "templates/inbox/l1/view/bottom_bar.html";
    $scope.footer_is_tabs = true;

    $scope.is_loading = true;

    $scope.loadContent = function() {
        Inbox.find($stateParams.message_id).success(function(data) {

            $scope.message = data.item;
            $scope.blocks = data.blocks;
            $scope.page_title = data.page_title;
            $scope.icon_url = data.icon_url;
            $scope.delete_message = data.delete_message;
            $scope.title_delete_message = data.title_delete_message;

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.confirmDeleteMessage = function() {

        var confirmPopup = $ionicPopup.confirm({
            title: $scope.title_delete_message,
            template: $scope.delete_message
        });

        confirmPopup.then(function(res) {
            if(res) {
                $scope.deleteMessage();
            }
        });

    };

    $scope.deleteMessage = function() {
        Inbox.deleteRootMessage($scope.message.message_id).success(function() {
            $ionicHistory.goBack();
        });
    };

    $scope.viewComments = function() {
        $state.go("inbox-comment-view", {message_id: $scope.message.message_id, value_id: $stateParams.value_id});
    };

    $scope.loadContent();
}).controller('InboxCommentViewController', function($scope, $state, $stateParams, Application, Customer, Inbox) {

    $scope.module_code = "inbox";

    $scope.show_bottom_bar = true;
    $scope.can_load_older_posts = true;

    $scope.collection = new Array();
    $scope.app_name = Application.app_name;

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.loadContent = function() {
        $scope.is_loading = true;

        Inbox.findComments($stateParams.message_id, Customer.id).success(function(data) {

            $scope.page_title = data.page_title;
            $scope.icon_url = data.icon_url;
            $scope.parent_is_deleted = data.parent_is_deleted;
            $scope.show_bottom_bar = !$scope.parent_is_deleted;

            $scope.collection = data.collection;

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.gotToPost = function() {
        $state.go("inbox-comment-post", {message_id: $stateParams.message_id, value_id: $stateParams.value_id});
    };

    $scope.loadContent();

}).controller('InboxCommentPostController', function($ionicHistory, $scope, $state, $stateParams, Customer, Inbox) {

    $scope.module_code = "inbox";

    $scope.reply = {};

    $scope.postComment = function() {

        if($scope.reply.message) {

            var data_to_send = {
                "message_id": $stateParams.message_id,
                "message": $scope.reply.message,
                "customer_id": Customer.id
            };

            Inbox.postComment(data_to_send).success(function() {
                $ionicHistory.goBack();
            });

        }
    };

});