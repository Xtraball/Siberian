<?php

abstract class Application_Model_Device_Ionic_Ios_Abstract extends Application_Model_Device_Abstract {

    protected $_default_bundle_name = "com.appsmobilecompany.base";

    abstract public function prepareResources();

    protected function _prepareRequest() {
        if(!defined("CRON")) {
            $request = new Siberian_Controller_Request_Http($this->_application->getUrl());
            $request->setPathInfo();
            $this->_request = $request;
        }
    }

    protected function _cpFolder() {

        /** Supprime le dossier s'il existe puis le créé */
        if(is_dir($this->_dest_source)) {
            Core_Model_Directory::delete($this->_dest_source);
        }
        mkdir($this->_dest_source, 0777, true);

        /** Copie les sources */
        Core_Model_Directory::duplicate($this->_orig_source, $this->_dest_source);

        return $this;
    }

    /**
     * @param $application Application_Model_Application|Previewer_Model_Previewer
     * @throws Exception
     */
    protected function ionicResources($application) {

        // Touch Icon
        $icons = array(
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-small.png'     => $application->getIcon(29, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-small@2x.png'  => $application->getIcon(58, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon.png'           => $application->getIcon(57, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon@2x.png'        => $application->getIcon(114, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-40.png'        => $application->getIcon(40, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-40@2x.png'     => $application->getIcon(80, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-50.png'        => $application->getIcon(50, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-50@2x.png'     => $application->getIcon(100, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-60.png'        => $application->getIcon(60, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-60@2x.png'     => $application->getIcon(120, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-60@3x.png'     => $application->getIcon(180, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-72.png'        => $application->getIcon(72, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-72@2x.png'     => $application->getIcon(144, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-76.png'        => $application->getIcon(76, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-76@2x.png'     => $application->getIcon(152, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-83.5@2x.png'   => $application->getIcon(152, null, true),
            $this->_dest_source_res.'/Images.xcassets/AppIcon.appiconset/icon-small@3x.png'  => $application->getIcon(120, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-1024.png'    => $application->getIcon(1024, null, true),
        );

        foreach($icons as $icon_dst => $icon_src) {

            if(stripos(Core_Model_Lib_Image::getMimeType($icon_src), "png") === false) {
                list($width, $height) = getimagesize($icon_src);
                $new_icon = imagecreatetruecolor($width, $height);
                $icon_resource = imagecreatefromstring(file_get_contents($icon_src));
                imagecopyresized($new_icon, $icon_resource, 0, 0, 0, 0, $width, $height, $width, $height);
                imagepng($new_icon, $icon_dst);
            } else if(!copy($icon_src, $icon_dst)) {
                throw new Exception(__('An error occured while copying your app icon. Please check the icon, try to send it again and try again.')."\n".$icon_src."\n".$icon_dst);
            }

        }

        //
        if (file_exists($this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-1024.png')) {
            $image = Siberian_Image::open($this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-1024.png');
            $image->resize(1024, 1024, 0xffffff);
            $image->save($this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-1024.png', 'png', 100);
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
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default~iphone.png'
                ), array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default@2x~iphone.png'
                )
            ),
            $startup_src_retina => array(
                array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-Portrait~ipad.png'
                ), array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-Portrait@2x~ipad.png'
                ), array(
                    "width" => 640,
                    "height" => 1136,
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-568h@2x~iphone.png'
                )
            ),
            $startup_src_iphone_6 => array(
                array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-667h.png'
                )
            ),
            $startup_src_iphone_6_plus => array(
                array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-736h.png'
                )
            ),
            $startup_src_ipad_retina => array(
                array(
                    "width" => 768,
                    "height" => 1024,
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-Portrait~ipad.png'
                ), array(
                    "width" => 768,
                    "height" => 1024,
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-Portrait~ipad.png'
                ), array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-Portrait@2x~ipad.png'
                ),
                /** Defaulting landcape splash */
                array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-Landscape-736h.png'
                ),array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-Landscape@2x~ipad.png'
                ),array(
                    "dst" => $this->_dest_source_res .'/Images.xcassets/LaunchImage.launchimage/Default-Landscape~ipad.png'
                )

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

                        $extension = pathinfo($image["dst"], PATHINFO_EXTENSION);
                        $image["dst"] = str_replace($extension, "png", $image["dst"]);

                        imagepng($newStartupImage, $image["dst"]);
                    } else {
                        if(!copy($startup_src, $image["dst"])) {
                            throw new Exception('An error occurred while generating the startup image. Please check the image, try to send it again and try again.', "{$image["width"]}x{$image["height"]}");
                        }
                    }

                    Siberian_Media::optimize($image["dst"]);
                }
            }
        }
        catch(Exception $e) {
            throw new Exception('An error occurred while generating the startup image. Please check the image, try to send it again and try again.');
        }

    }

    protected function buildPList() {

        $plist_path = $this->_dest_source_amc.'/AppsMobileCompany-Info.plist';
        $xml = simplexml_load_file($plist_path);
        $str = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd"><plist version="1.0"><dict></dict></plist>';
        $this->_new_xml = simplexml_load_string($str);
        $this->_parsePList($xml->dict, $this->_new_xml->dict, $this->_new_xml);

        $plist = fopen($plist_path, 'w+');
        if(!$plist) {
            throw new Exception("An error occurred while copying the source files ({$plist_path})");
        }

        fwrite($plist, $this->_new_xml->asXML());
        fclose($plist);

        /** Device orientations */
        $search = "</dict>";
        $replace = "<key>UIRequiresFullScreen</key><true/><key>UISupportedInterfaceOrientations</key><array><string>UIInterfaceOrientationPortrait</string><string>UIInterfaceOrientationLandscapeLeft</string><string>UIInterfaceOrientationLandscapeRight</string></array><key>UISupportedInterfaceOrientations~ipad</key><array><string>UIInterfaceOrientationPortrait</string><string>UIInterfaceOrientationLandscapeLeft</string><string>UIInterfaceOrientationPortraitUpsideDown</string><string>UIInterfaceOrientationLandscapeRight</string></array></dict>";

        $this->__replace(array($search => $replace), $plist_path);

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

                    $value = $this->_application_name;

                } else if($lastValue == 'CFBundleIdentifier') {

                    $value = $this->_package_name;

                } else if($lastValue == "AppId") {

                    $value = $this->_application_id;

                } else if(stripos($lastValue, "url_") !== false) {

                    $value = $this->__getUrlValue($lastValue);

                } else if(stripos($lastValue, "CFBundleVersion") !== false) {

                    if(isset($this->_previewer)) {
                        $this->_previewer->setIosBuildNumber($this->_previewer->getIosBuildNumber()+1)->save();
                        $value = $this->_previewer->getIosVersion().".".$this->_previewer->getIosBuildNumber();
                    } else {
                        $this->getDevice()->setBuildNumber($this->getDevice()->getBuildNumber()+1)->save();
                        $value = $this->getDevice()->getVersion().".".$this->getDevice()->getBuildNumber();
                    }

                } else if(stripos($lastValue, "CFBundleShortVersionString") !== false) {

                    if(isset($this->_previewer)) {
                        $value = $this->_previewer->getIosVersion();
                    } else {
                        $value = $this->getDevice()->getVersion();
                    }

                } else if($lastValue == "UIStatusBarHidden") {

                    $key = $this->_application->getIosStatusBarIsHidden() ? "true" : "false";
                    $value = null;

                } else if($lastValue == "UIViewControllerBasedStatusBarAppearance") {

                    $key = $this->_application->getIosStatusBarIsHidden() ? "false" : "true";
                    $value = null;

                }

                $newNode->addChild($key, $value);
                $lastValue = $value;
            }

        }

    }

    private function __getUrlValue($key) {

        if(defined("CRON")) {
            $scheme = "http";
            $http_host = $this->getDevice()->getHost();
            $base_url = "/";

        } else {
            $scheme = $this->_request->getScheme();
            $http_host = $this->_request->getHttpHost();
            $base_url = ltrim($this->_request->getBaseUrl(), "/");
        }

        $value = null;

        switch($key) {
            case "url_scheme": $value = $scheme; break;
            case "url_domain": $value = $http_host; break;
            case "url_path": $value = $base_url; break;
            case "url_key":
                if(!defined("CRON") && $this->_request->useApplicationKey()) {
                    $value = $this->getApplication()->getKey();
                }
                break;
            default: $value = "";
        }

        return $value;
    }

    protected function _admob() {
        $app_js_path = $this->_dest_source."/www/js/app.js";

        $this->__replace(array("#,\s*isTesting:\s+true#ims" => ""), $app_js_path, true);
    }
}
