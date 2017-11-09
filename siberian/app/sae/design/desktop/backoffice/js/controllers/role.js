App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/acl/backoffice_role_list", {
        controller: 'RoleListController',
        templateUrl: BASE_URL+"/acl/backoffice_role_list/template"
    }).when(BASE_URL+"/acl/backoffice_role_edit/role_id/:role_id", {
        controller: 'RoleEditController',
        templateUrl: BASE_URL+"/acl/backoffice_role_edit/template",
        code: "role-edit"
    }).when(BASE_URL+"/acl/backoffice_role_edit", {
        controller: 'RoleEditController',
        templateUrl: BASE_URL+"/acl/backoffice_role_edit/template",
        code: "role-edit"
    });

}).controller("RoleListController", function($scope, $location, Header, SectionButton, Role) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.content_loader_is_visible = true;

    $scope.button = new SectionButton(function() {
        $location.path("acl/backoffice_role_edit");
    });

    Role.loadListData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    });

    Role.findAll().success(function(data) {
        $scope.roles = data;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
    });

    $scope.deleteRole = function(role_id) {
        Role.delete(role_id).success(function(data){
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            var new_role_list = new Array();
            angular.forEach($scope.roles,function(role){
                if(role.id != role_id){
                    new_role_list.push(role);
                }
            });
            $scope.roles = new_role_list;
        });
    };


}).controller("RoleEditController", function($scope, $location, $routeParams, $window, Header, Role, Url) {

    if($routeParams.role_id==1) {
        $location.path(Url.get("acl/backoffice_role_list"));
    } else {
        $scope.header = new Header();
        $scope.header.button.left.is_visible = false;
        $scope.content_loader_is_visible = true;

        $scope.denied_resources = {};

        Role.loadListData().success(function (data) {
            $scope.header.title = data.title;
            $scope.header.icon = data.icon;
        });

        Role.find($routeParams.role_id).success(function (data) {
            
            $scope.section_title = data.title;
            $scope.role = data.role;
            $scope.resources = data.resources;

            $scope.parent_resources = new Array();
            angular.forEach($scope.resources, function(resource) {
                $scope.parent_resources[resource.code] = false;
            });

            $scope.__prepareParents();            

        }).finally(function () {
            $scope.content_loader_is_visible = false;
        });

        $scope.save = function() {

            $scope.__prepareParents(null, true);

            var role = {
                "role": $scope.role,
                "resources": $scope.resources
            };

            Role.save(role).success(function(data) {
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
                $location.path(Url.get("acl/backoffice_role_list"));
            }).error(function(data) {
                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;

                $scope.__prepareParents();
            });

        };

        $scope.toggleIsAllowed = function(resource, is_allowed) {

            if(!angular.isDefined(is_allowed)) {
                is_allowed = resource.is_allowed;
            }

            if(is_allowed) {
                $scope._toggleParentsIsAllowed(resource);    
            }

            $scope._toggleChildrenIsAllowed(resource, is_allowed);

        };

        $scope._toggleParentsIsAllowed = function(resource) {

            for(var i = 0; i < 10; i++) {
                if(resource) {
                    resource.is_allowed = true;
                    var resource = resource.parent;
                }
            }

        };
        
        $scope._toggleChildrenIsAllowed = function(resource, is_allowed) {

            resource.is_allowed = is_allowed;

            if(resource.children) {
                resource.children_are_visible = true;
                angular.forEach(resource.children, function(child) {
                    $scope.toggleIsAllowed(child, is_allowed);
                });
            }

        };

        $scope.__prepareParents = function(parent, remove_parent) {

            var resources = parent ? parent.children : $scope.resources;

            angular.forEach(resources, function(child) {

                child.parent = remove_parent ? null : parent;
                if(child.children) {
                    $scope.__prepareParents(child, remove_parent);
                }
            });

        };

    }


});
