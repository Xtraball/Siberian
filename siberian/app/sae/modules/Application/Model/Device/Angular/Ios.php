<?php

class Application_Model_Device_Angular_Ios extends Application_Model_Device_Abstract {

    const SOURCE_FOLDER = "/var/apps/angular/iphone/Siberian";
    const DEST_FOLDER = "/var/tmp/applications/angular/iphone/%s/Siberian";

    protected $_current_version = '1.0';
    protected $_dst;
    protected $_base_dst;
    protected $_zipname;
    protected $_new_xml;
    protected $_request;

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_os_name = "ios";
        return $this;
    }

    public static $_store_categories = array(
        1 => "Business",
        2 => "Catalogs",
        3 => "Education",
        4 => "Entertainment",
        5 => "Finance",
        6 => "Food & Drink",
        7 => "Games",
        8 => "Health & Fitness",
        9 => "Lifestyle",
        10 => "Medical",
        11 => "Audio",
        12 => "Navigation",
        13 => "News",
        14 => "Photo & Video",
        15 => "Productivity",
        16 => "Reference",
        17 => "Social Networking",
        18 => "Sports",
        19 => "Travel",
        20 => "Utilities",
        21 => "Weather",
        22 => "Book"
    );

    public static function getStoreCategeories() {
        $categories = array();
        foreach(self::$_store_categories as $key => $category) {
            $category_name = parent::_($category);

            $categories[$category_name] = new Core_Model_Default(array(
                'id' => $key,
                'name' => parent::_($category_name),
            ));
        }

        ksort($categories);

        return $categories;
    }

    public static function getStoreCategory($cat_id) {

        foreach(self::getStoreCategeories() as $category) {
            if($category->getId() == $cat_id) return $category;
        }

        return new Core_Model_Default();
    }

    public function getCurrentVersion() {
        return $this->_current_version;
    }

    public function getStoreName() {
        return "App Store";
    }

    public function getBrandName() {
        return "Apple";
    }

    public function prepareResources() {

        $this->_prepareRequest();
        $this->_cpFolder();
        $this->_preparePList();
        $this->_copyImages();
        $zip = $this->_zipFolder();

        return $zip;
    }

    public function getResources($application) {

        $umask = umask(0);

        self::setApplication($application);
        $src = $this->prepareResources();

        umask($umask);

        return $src;

    }

    protected function _prepareRequest() {
        if(!defined("CRON")) {
            $request = new Siberian_Controller_Request_Http($this->getApplication()->getUrl());
            $request->setPathInfo();
            $this->_request = $request;
        }
    }

    protected function _cpFolder() {

        $formatted_name = Core_Model_Lib_String::format($this->getApplication()->getName(), true);
        if(!$formatted_name) {
            $formatted_name = $this->getApplication()->getId();
            $zip_name = $formatted_name;
        } else {
            $zip_name = $formatted_name;
            $formatted_name .= "-".$this->getApplication()->getId();
        }

        $src = Core_Model_Directory::getBasePathTo(self::SOURCE_FOLDER);
        if($this->getDevice()->getExcludeAds()) {
            $src .= " No Ads";
        }
        
        $dst = Core_Model_Directory::getBasePathTo(self::DEST_FOLDER);
        $dst = sprintf($dst, $formatted_name);

        // Supprime le dossier s'il existe puis le créé
        if(is_dir($dst)) Core_Model_Directory::delete($dst);
        mkdir($dst, 0775, true);

        // Copie les sources
        Core_Model_Directory::duplicate($src, $dst);

        $this->_zipname = $zip_name.'_ios_source';
        $this->_dst = $dst.'/Apps Mobile Company';

        $this->_base_dst = $dst;
        return $this;

    }

    protected function _preparePList() {

        $file = $this->_dst.'/Apps Mobile Company-Info.plist';
        $xml = simplexml_load_file($file);
        $str = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd"><plist version="1.0"><dict></dict></plist>';
        $this->_new_xml = simplexml_load_string($str);
        $this->_parsePList($xml->dict, $this->_new_xml->dict, $this->_new_xml);

        $plist = fopen($file, 'w+');
        if(!$plist) {
            throw new Exception('An error occurred while copying the source files ('.$file.')');
        }

        fwrite($plist, $this->_new_xml->asXML());
        fclose($plist);

        return $this;

    }

    protected function _parsePList($node, $newNode) {

        $lastValue = '';

        foreach($node->children() as $key => $child) {

            $value = (string) $child;
            if(count($child->children()) > 0) {
                $this->_parsePList($child, $newNode->addChild($key));
            } else {
                if($lastValue == 'CFBundleDisplayName') {
                    $value = $this->getApplication()->getName();
                } else if($lastValue == 'CFBundleIdentifier') {
                    $value = $this->getApplication()->getBundleId();
                } else if($lastValue == "AppId") {
                    $value = $this->getApplication()->getId();
                } else if(stripos($lastValue, "url_") !== false) {
                    $value = $this->__getUrlValue($lastValue);
                } else if(stripos($lastValue, "CFBundleVersion") !== false) {
                    $version = explode(".", $this->getDevice()->getVersion());
                    $value = !empty($version[0]) && !empty($version[1]) ? $version[0].".".$version[1] : "1.0";
                } else if(stripos($lastValue, "CFBundleShortVersionString") !== false) {
                    $version = explode(".", $this->getDevice()->getVersion());
                    $value = !empty($version[0]) ? $version[0] : 1;
                } else if($lastValue == "UIStatusBarHidden") {
                    $key = $this->getApplication()->getIosStatusBarIsHidden() ? "true" : "false";
                    $value = null;
                } else if($lastValue == "UIViewControllerBasedStatusBarAppearance") {
                    $key = $this->getApplication()->getIosStatusBarIsHidden() ? "false" : "true";
                    $value = null;
                }

                $newNode->addChild($key, $value);
                $lastValue = $value;
            }

        }

    }

    protected function _copyImages() {

        $application = $this->getApplication();

        // Touch Icon
        $icons = array(
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/29x29.png' => $application->getIcon(29, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/29x29-1.png' => $application->getIcon(29, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/29x29@2x.png' => $application->getIcon(58, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/29x29@2x-1.png' => $application->getIcon(58, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/29x29@3x.png' => $application->getIcon(87, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/40x40.png' => $application->getIcon(40, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/40x40@2x.png' => $application->getIcon(80, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/40x40@2x-1.png' => $application->getIcon(80, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/40x40@3x.png' => $application->getIcon(120, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/50x50.png' => $application->getIcon(50, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/50x50@2x.png' => $application->getIcon(100, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/72x72.png' => $application->getIcon(72, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/72x72@2x.png' => $application->getIcon(144, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/76x76.png' => $application->getIcon(76, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/76x76@2x.png' => $application->getIcon(152, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/57x57.png' => $application->getIcon(57, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/57x57@2x.png' => $application->getIcon(114, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/60x60@2x.png' => $application->getIcon(120, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/60x60@3x.png' => $application->getIcon(180, null, true),
            $this->_dst.'/../TouchIcon.png' => $application->getAppStoreIcon(true)
        );

        foreach($icons as $icon_dst => $icon_src) {
            if(!copy($icon_src, $icon_dst)) {
                throw new Exception(__('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
            }
        }


        // Startup Images
        $startup_src = $application->getStartupImageUrl("standard", true);
        $startup_src_retina = $application->getStartupImageUrl("retina", true);
        $startup_src_iphone_6 = $application->getStartupImageUrl("iphone_6", true);
        $startup_src_iphone_6_plus = $application->getStartupImageUrl("iphone_6_plus", true);
        $startup_src_ipad_retina = $application->getStartupImageUrl("ipad_retina", true);

        $startups = array(
            $startup_src => array(
                array(
                    "width" => 320,
                    "height" => 480,
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/320x480.png'
                ), array(
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/640x960.png'
                ), array(
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/640x960-1.png'
                ),
            ),
            $startup_src_retina => array(
                array(
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/640x1136.png'
                ), array(
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/640x1136-1.png'
                )
            ),
            $startup_src_iphone_6 => array(
                array(
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/750x1334.png'
                )
            ),
            $startup_src_iphone_6_plus => array(
                array(
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/1242x2208.png'
                )
            ),
            $startup_src_ipad_retina => array(
                array(
                    "width" => 768,
                    "height" => 1024,
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/768x1024.png'
                ), array(
                    "width" => 768,
                    "height" => 1024,
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/768x1024-1.png'
                ), array(
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/1536x2048.png'
                ), array(
                    "dst" => $this->_dst .'/Images.xcassets/LaunchImage.launchimage/1536x2048-1.png'
                ),

            ),
        );

        try {
            foreach($startups as $startup_src => $images) {
                foreach($images as $image) {
                    if(!empty($image["width"]) OR Core_Model_Lib_Image::getMimeType($startup_src) != 'image/png') {
                        list($width, $height) = getimagesize($startup_src);
                        if(empty($image["width"])) $image["width"] = $width;
                        if(empty($image["height"])) $image["height"] = $height;
                        $newStartupImage = imagecreatetruecolor($image["width"], $image["height"]);
                        $startupSrc = imagecreatefromstring(file_get_contents($startup_src));
                        imagecopyresized($newStartupImage, $startupSrc, 0, 0, 0, 0, $image["width"], $image["height"], $width, $height);
                        imagepng($newStartupImage, $image["dst"]);
                    } else {
                        if(!copy($startup_src, $image["dst"])) {
                            throw new Exception('An error occurred while generating the startup image. Please check the image, try to send it again and try again.', "{$image["width"]}x{$image["height"]}");
                        }
                    }
                }
            }
        }
        catch(Exception $e) {
            throw new Exception('An error occurred while generating the startup image. Please check the image, try to send it again and try again.');
        }

    }

    protected function _zipFolder() {

        $src = $this->_base_dst;
        $filepath = $src.'/'.$this->_zipname.'.zip';
        
        Core_Model_Directory::zip($this->_base_dst, $filepath);

        if(!file_exists($filepath)) {
            throw new Exception('An error occurred during the creation of the archive ('.$filepath.')');
        }
        
        return $filepath;

    }

    private function __getUrlValue($key) {

        if(!defined("CRON")) {
            $scheme = $this->_request->getScheme();
            $http_host = $this->_request->getHttpHost();
            $base_url = $this->_request->getBaseUrl();
            $use_key = $this->_request->useApplicationKey();
        } else {
            $scheme = "http";
            $http_host = $this->getDevice()->getHost();
            $base_url = "/";
            $use_key = true;
        }

        $value = null;
        
        switch($key) {
            case "url_scheme": $value = $scheme; break;
            case "url_domain": $value = $http_host; break;
            case "url_path": $value = ltrim($base_url, "/"); break;
            case "url_key":
                if($use_key) {
                    $value = $this->getApplication()->getKey();
                }
                break;
            default: $value = "";
        }

        return $value;
    }

}
