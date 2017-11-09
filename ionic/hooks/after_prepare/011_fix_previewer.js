#!/usr/bin/env node

// Add Platform Class
// v1.0
// Automatically adds the platform class to the body tag
// after the `prepare` command. By placing the platform CSS classes
// directly in the HTML built for the platform, it speeds up
// rendering the correct layout/style for the specific platform
// instead of waiting for the JS to figure out the correct classes.

var fs = require('fs');
var path = require('path');
var sh = require('shelljs');

var rootdir = process.argv[2];

if (rootdir) {

  // go through each of the platform directories that have been prepared
  var platforms = (process.env.CORDOVA_PLATFORMS ? process.env.CORDOVA_PLATFORMS.split(',') : []);

  for(var x=0; x<platforms.length; x++) {
    // open up the index.html file at the www root
    try {
      var platform = platforms[x].trim().toLowerCase();

      var config_bck = path.join('platforms', platform, 'config.bck.xml');
      if(platform === 'android-previewer') {
        var config = path.join('platforms', platform, 'res', 'xml', 'config.xml');
        sh.cp("-f", config_bck, config);
      } else if((platform === 'ios-previewer') || (platform === 'ios-noads')) {
        var config = path.join('platforms', platform, 'AppsMobileCompany', 'config.xml');
        sh.cp("-f", config_bck, config);
      }

    } catch(e) {
      process.stdout.write(e);
    }
  }

}
