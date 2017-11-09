App.service("Sidebar", function(SidebarInstances) {

    var factory = function(object_id) {

        this.object_id = object_id;

        if(SidebarInstances[object_id]) return SidebarInstances[object_id];

        this.showFirstItem = function(collection) {

            if(!collection.length) {
                this.is_loading = false;
                return this;
            }

            for(var i = 0; i < collection.length; i++) {
                if(!angular.isDefined(collection[i].enable_load_onscroll)) {
                    collection[i].enable_load_onscroll = true;
                }
            }

            if(this.current_item) {
                var item = this.current_item;
                this.current_item = null;
                this.showItem(item);
                return this;
            }

            if(this.first_item) return;

            for(var i in collection) {
                var item = collection[i];
                if(item.children && item.children.length) {
                    this.showFirstItem(item.children);
                } else {
                    this.first_item = item;
                    break;
                }
            }

            if(this.first_item && !this.current_item) {
                this.showItem(this.first_item);
            }

            return this;

        };

        this.showItem = function(item) {

            if(this.current_item == item) return;

            if(item.children) {
                item.show_children = !item.show_children;
            } else {
                this.loadItem(item);
            }

        };

        this.loadItem = function(item) {

        };

        this.toggle = function() {
            if(!this.current_item) return;
            this.show = !this.show;
        };

        this.reset = function() {
            this.is_loading = true;
            this.collection = new Array();
            this.current_item = null;
            this.first_item = null;
            this.show = false;
        };

        this.reset();

        SidebarInstances[object_id] = this;
    };

    return factory;

}).factory("SidebarInstances", function() {
    return {};
});