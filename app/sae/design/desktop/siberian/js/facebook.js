(function(d){
    var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
    js = d.createElement('script'); js.id = id; js.async = true;
    js.src = "//connect.facebook.net/fr_FR/all.js";
    d.getElementsByTagName('head')[0].appendChild(js);
}(document));

var fbObject = {isLoaded: false};

//function fbLogin() {
//    FB.login(function(response) {
//        if(response.authResponse) {
//
//        }
//    }, {scope: fbObject.perms});
//}