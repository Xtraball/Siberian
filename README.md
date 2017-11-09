# SiberianCMS: Xtraball internal developer package.

---
## Installation

Checkout xtraball/siberiancms 
`git clone --recursive git@gitlab.xtraball.com:xtraball/siberiancms.git`

**note:** *the `--recursive` will also checkout the git submodules automatically.*
	
1. Run `npm install` then follow the instructions to update your local shell.

2. Run `siberian init` to init your local project.
    
**note:** *if the `modules, plugins & platforms` were not correctly checked out, run `git submodule update --recursive` or `git submodule update --init --recursive`

## Ionic server 

Run `siberian ions` to start the server.

The **ionic** project is located in the folder named `ionic`

## Web server

The project root path is located in the folder `siberian`

Here are the default configurations for both **apache** & **nginx**, thus the command `siberian init` will generate a configuration file for your local environment

`apache.default` or `nginx.default`

* Apache

```
<VirtualHost [IP]:80>
        ServerName [domain.com]

		CustomLog [/path/to/siberiancms]/siberian/var/log/httpd.access_log combined
		ErrorLog [/path/to/siberiancms]/siberian/var/log/httpd.error_log

		DirectoryIndex index.php

        DocumentRoot [/path/to/siberiancms]/siberian

        <Directory [/path/to/siberiancms]/siberian>
                Options Indexes FollowSymLinks
                AllowOverride all
        </Directory>

</VirtualHost>
```

* Nginx

```
server {
    listen 80;

	root [/path/to/siberiancms]/siberian;
		
	access_log [/path/to/siberiancms]/siberian/var/log/nginx.access_log;
	error_log [/path/to/siberiancms]/siberian/var/log/nginx.error_log;

	index index.php index.html index.htm index.nginx-debian.html;

	server_name [domain.com];
	
	location ~ ^/app/configs {
        deny all;
    }
    
    location ~ ^/var/apps/certificates {
        deny all;
    }

	location / {
		try_files $uri /index.php?$query_string;
	}

	location ~ \.php$ {
		fastcgi_index index.php;
		fastcgi_pass 127.0.0.1:9000;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		include fastcgi_params;
		fastcgi_buffers 256 128k;
		fastcgi_connect_timeout 300s;
		fastcgi_send_timeout 300s;
		fastcgi_read_timeout 300s;
	}

    location ~* ^.+.(js|css|png|jpg|jpeg|gif|ico|html)$ {
		access_log        off;
		log_not_found     off;
		expires           0;
	}
	
	location ~ /\. {
		access_log off;
		log_not_found off;
		deny all;
	}

	gzip on;
	gzip_min_length  1000;
	gzip_proxied any;
	gzip_types text/plain application/xml text/css text/js application/x-javascript;
	
	client_max_body_size 200M;

}
```

## Development

### Platforms

If a custom development is needed for a platform, `cd` to the folder, then push to the local platform, the branch is named `siberian`

- Platforms templates used to build/rebuild are installed from this directory, this ensure the platforms are synced & up-to-date everytime.

- Android* `platforms/cdv-siberian-android`
- iOS* `platforms/cdv-siberian-ios`

The other platforms specific to Siberian which are `cdv-siberian-android-previewer`, `cdv-siberian-ios-previewer` & `cdv-siberian-ios-noads` are automatically synced from their respective parents.

- Rebuilding a platform
    1. run `siberian rebuild platformName` where platformName is `android | android-previewer |ios | ios-noads | ios-previewer | browser`
    2. Wait & see ... the magic happens !

### Plugins

Every plugin used in the project is forked into our GitLab CE, they are added as submodules in the folder `plugins`

A default branch named `siberian` is used to track and lock our modifications.

- Platform plugin's are installed from this directory, this ensure the plugins are synced & up-to-date everytime.

- If an update is required, like migrating a plugin to the last version here is the procedure:
    1. Duplicate `siberian` branch to a tag named `tags/siberian-x.x.x` where x.x.x is the current Siberian version.
    2. Ensure the fork is synced with the master (Xtraball Bot sync forks every day at 2:00AM)
    3. Merge the required version (or master) to `siberian`
    4. Push updates. (you can `cd` to the plugin directory and pull|push|merge|etc... like an indepenant git tree)
    
### Modules

Our standalone modules are tracked into the folder `modules` every module has it's own git, and is versioned independantly of the Siberian Editions

They have their own git-flow convention for developing, mastering & releasing, like the Siberian application itself.

- Structure of a module
    1. @TODO
    
- Modules list
    0. MISC (this directory is for installed & custom modules)
        - Inbox
    1. SAE (Core)
        - Acl
        - Admin
        - Api
        - Application
        - Backoffice
        - Catalog
        - Cms
        - Codescan
        - Comment
        - Contact
        - Core
        - Customer
        - Event
        - Fanwall
        - Folder
        - Form
        - Front
        - Installer
        - LoyaltyCard
        - Map
        - Maps
        - Mcommerce
        - Media
        - Message
        - Padlock
        - Payment
        - Places
        - Preview
        - Promotion
        - Push
        - Rss
        - Social
        - Socialgaming
        - Sourcecode
        - System
        - Tax
        - Template
        - Tip
        - Topic
        - Translation
        - Weather
        - Weblink
        - Wordpress
    2. MAE
    3. PE
        - Sales
        - Subscription
        - Whitelabel

### Best-practices

Todo ...

### Guidelines

Todo ...