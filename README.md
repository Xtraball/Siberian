# Siberian SAE (Single App Edition)

![welcome](docs/siberiancms.png)

## Community forums & Roadmap

* [Public Roadmap](https://www.siberiancms.com/community/d/745-roadmap-4-16-4-17)
* [Forums](https://www.siberiancms.com/community/)
* [Guidelines](https://www.siberiancms.com/community/d/79-siberian-community-guidelines)

## Documentation

* [User documentation](http://doc.siberiancms.com)
* [Developer documentation](http://developer.siberiancms.com)

## Requirements

### Software

* Production server OS: `Linux`

* Local development:

    * `OSX` with [homebrew](http://brew.sh/)
    * `Windows` with [bash](https://docs.microsoft.com/fr-fr/windows/wsl/install-win10) 
    
* NodeJS
    
* OpenSSL >=1.0.1

    * with TLS v1.2 support

* [Apache](#apache) or [Nginx](#nginx)

* PHP

    * Version: 7.3
    
    * Extensions: `gd`, `pdo_mysql`, `SimpleXML`, `curl`, `dom`, `SQLite3`.
    
    * Functions: `exec()`
    
    * Parameters: `allow_url_fopen = On`, `memory_limit >= 128M`, `post_max_size = 100M`, `upload_max_filesize = 100m`, `max_execution_time = 300`

* MySQL/MariaDB >=5.5 with InnoDB/XtraDB engine

* Binaries: 

    * required: `zip`, `unzip`

    * optional: `pngquant` or `optipng`, `jpegoptim`, `ClamAV`

### Configuration

1. First you will need to either checkout the project `git clone https://github.com/Xtraball/Siberian.git`

    or download the [zip archive](https://github.com/Xtraball/Siberian/archive/master.zip) then extract it on your webserver.

2. Run `npm install` then follow the instructions to update your local shell.

3. Go into `ionic` folder then run `npm install` too.

4. Run `./bin/install` to hook custom modifications on the installed node_modules.

5. Run `./sb init` to init your local project.

6. Configure your environment with either [apache](#apache) or [nginx](#nginx) with the given generated templates from step 4.

When you're done with the previous steps, reload your web server, then install using the [Web installer](#Web-installer)

### Web installer

* Go to `http://yourdomain.tld` then follow the instructions

![welcome](docs/install-sae.gif)


# Developer package & resources.

---

## Developers

### Platforms

If a custom development is needed for a platform, `cd` to the folder, then push to the local platform, the branch is named `siberian`

- Platforms templates used to build/rebuild are installed from this directory, this ensure the platforms are synced & up-to-date everytime.

- Browser `platforms/Browser`
- Android `platforms/Android`
- iOS `platforms/Ios`

- Rebuilding a platform
    1. run `siberian rebuild platformName` where platformName is `android | ios | browser`

### Note: Important

Siberian uses a Cordova fork for its applications base, we provide pre-built android & ios binaries for convenience as not everyone owns and can build using a Mac.

If you need to rebuild native source code for all platforms, you must have a Mac and Xcode with the Command-Line Tools installed.

**Pre-built binaries allows you to customize all the HTML/JS/CSS Stack of the Apps without the need of a Mac.**

### Plugins

Every plugin used in the project is forked on GitHub, they are added as submodules in the folder `plugins`

A default branch named `siberian` is used to track and lock our modifications.
    
### Modules

Our standalone modules are tracked into the folder `modules` every module has it's own git, and is versioned independantly of the Siberian Editions


# Siberian command-line interface Help

Available commands are: 

|Command|Description|
|---|---|
|alias|Prints bash aliases to help development|
|clearcache, cc|Clear siberian/var/cache|
|clearlog, cl|Clear siberian/var/log|
|cleanlang|Clean-up duplicates & sort languages CSV files|
|db|Check if databases exists, otherwise create them|
|export-db|Export db tables to schema files|
|init|Initializes DB, project, settings.|
|install|Install forks for cordova-lib.|
|icons|Build ionicons font|
||- install: install required dependencies (OSX Only).|
||icons [install]|
|ions|Start ionic serve in background|
|rebuild|Rebuild a platform  (requires Android SDK & Xcode, Command-Line Tools):|
||- debug: option will show more informations.|
||- copy: copy platform to siberian/var/apps.|
||- no-manifest: don't call the rebuild manifest hook.|
||rebuild <platform> [copy] [debug] [no-manifest]|
|rebuild-all|Rebuild all platforms (requires Android SDK & Xcode, Command-Line Tools)|
|syncmodule, sm|Resync a module in the Application|
|type|Switch the Application type 'sae|mae|pe' or print the current if blank|
||note: clearcache is called when changing type.|
||- reset: optional, will set is_installed to 0.|
||- empty: optional, clear all the database.|
||type [type] [reset] [empty]|
|test|Test PHP syntax|
|pack|Pack a module into zip, file is located in ./packages/modules/|
||- If using from a module forlders module_name is optional|
||pack <module_name>|
|packall|Pack all referenced modules|
|prepare|Prepare a platform (Doesn't requires Android SDK & Xcode, it's suitable for any HTML/JS/CSS Customization in the Apps):|
||- debug: option will show more informations.|
||- copy: copy platform to siberian/var/apps.|
||- no-manifest: don't call the rebuild manifest hook.|
||prepare <platform> [copy] [debug] [no-manifest]|
|manifest|Rebuilds app manifest|
|moduleversion, mver|Update all module version to <version> or only the specified one, in database.|
||- module_name is case-insensitive and is searched with LIKE %module_name%|
||- module_name is optional and if empty all modules versions are changed|
||moduleversion <version> [module_name]|
|npm|Hook for npm version.|
||npm <version>|
|prod|Switch the Application mode to 'production'.|
|dev|Switch the Application mode to 'development'.|
|version|Prints the current Siberia version.|
|linkmodule, lm|Symlink a module from ./modules/ to ./siberian/app/local/modules/|
||lm <module>|
|unlinkmodule, ulm|Remove module symlink|
||ulm <module>|
|syncmodule, sm |Sync all sub-modules/platforms/plugins from git|
