<?php

/**
 * Class Application_Model_Device_Ionic_Ios_Abstract
 */
abstract class Application_Model_Device_Ionic_Ios_Abstract extends Application_Model_Device_Abstract
{
    /**
     * @var string
     */
    protected $_default_bundle_name = 'com.appsmobilecompany.base';

    /**
     * @var Application_Model_Application
     */
    public $currentApplication;

    /**
     * @return mixed
     */
    abstract public function prepareResources();

    /**
     * @throws Zend_Controller_Request_Exception
     */
    protected function _prepareRequest()
    {
        if (!defined('CRON')) {
            $request = new Siberian_Controller_Request_Http($this->currentApplication->getUrl());
            $request->setPathInfo();
            $this->_request = $request;
        }
    }

    /**
     * @return $this
     */
    protected function _cpFolder()
    {
        // Clean-up previously created folders if exists!
        if (is_dir($this->_dest_source)) {
            Core_Model_Directory::delete($this->_dest_source);
        }
        mkdir($this->_dest_source, 0777, true);

        // Duplicate sources folder!
        Core_Model_Directory::duplicate($this->_orig_source, $this->_dest_source);

        return $this;
    }

    /**
     * @return $this
     */
    protected function _cleanAssets()
    {
        exec("rm -Rf '{$this->_dest_source_amc}/../www/css'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/js/controllers'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/js/directives'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/js/factory'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/js/features'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/js/filters'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/js/libraries'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/js/providers'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/js/services'");
        exec("rm -f '{$this->_dest_source_amc}/../www/js/MusicControls.js'");
        exec("rm -f '{$this->_dest_source_amc}/../www/js/app.js'");
        exec("rm -f '{$this->_dest_source_amc}/../www/js/utils/features.js'");
        exec("rm -f '{$this->_dest_source_amc}/../www/js/utils/form-post.js'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/lib/ionic/css'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/lib/ionic/js'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/lib/ionic/scss'");
        exec("rm -f '{$this->_dest_source_amc}/../www/lib/ionic/version.json'");
        exec("rm -Rf '{$this->_dest_source_amc}/../www/templates'");

        return $this;
    }

    /**
     * @param $application Application_Model_Application|Previewer_Model_Previewer
     * @throws Exception
     */
    protected function ionicResources($application)
    {
        // Touch Icons!
        $icons = [
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-small.png' =>
                $application->getIcon(29, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-small@2x.png' =>
                $application->getIcon(58, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon@2x.png' =>
                $application->getIcon(114, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-40.png' =>
                $application->getIcon(40, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-40@2x.png' =>
                $application->getIcon(80, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-50.png' =>
                $application->getIcon(50, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-50@2x.png' =>
                $application->getIcon(100, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-60.png' =>
                $application->getIcon(60, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-60@2x.png' =>
                $application->getIcon(120, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-72.png' =>
                $application->getIcon(72, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-72@2x.png' =>
                $application->getIcon(144, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-76.png' =>
                $application->getIcon(76, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-76@2x.png' =>
                $application->getIcon(152, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-83.5@2x.png' =>
                $application->getIcon(167, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-small@3x.png' =>
                $application->getIcon(87, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon.png' =>
                $application->getIcon(57, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-60@3x.png' =>
                $application->getIcon(180, null, true),
            $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-1024.png' =>
                $application->getIcon(1024, null, true),
        ];

        foreach ($icons as $iconDst => $iconSrc) {
            if (stripos(Core_Model_Lib_Image::getMimeType($iconSrc), "png") === false) {
                list($width, $height) = getimagesize($iconSrc);
                $newIcon = imagecreatetruecolor($width, $height);
                $iconResource = imagecreatefromstring(file_get_contents($iconSrc));
                imagecopyresized($newIcon, $iconResource, 0, 0, 0, 0, $width, $height, $width, $height);
                imagepng($newIcon, $iconDst);
            } else if (!copy($iconSrc, $iconDst)) {
                throw new \Siberian\Exception(
                    __('An error occured while copying your app icon. Please check the icon, try to send it again and try again.') .
                    "\n" . $iconSrc . "\n" . $iconDst);
            }

        }

        //
        if (file_exists($this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-1024.png')) {
            $image = Siberian_Image::open(
                $this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-1024.png');
            $image->resize(1024, 1024, 0xffffff);
            $image->save($this->_dest_source_res . '/Images.xcassets/AppIcon.appiconset/icon-1024.png', 'png', 100);
        }


        /** Clean up screen.xxx */
        array_map('unlink', glob("{$this->_dest_source_res}/Images.xcassets/LaunchImage.launchimage/*.png"));

        // Startup Images!
        $universal = path($application->getStartupBackgroundUnified());

        $tmpDest = $this->_dest_source_res;

        try {
            // Convert to jpeg
            $jpegStartup = Siberian_Image::open($universal);
            $_tmpStartup = Core_Model_Directory::getBasePathTo('/var/tmp/' . uniqid('', false) . '.jpg');
            $jpegStartup->save($_tmpStartup, 'jpeg', 70);

            $destStartup = $tmpDest . '/Images.xcassets/LaunchStoryboard.imageset/Default@2x~universal~anyany.jpg';

            Siberian_Media::optimize($_tmpStartup);

            if (!copy($_tmpStartup, $destStartup)) {
                throw new Exception('An error occurred while generating the startup image. Please check the image, try to send it again and try again.');
            }
        } catch (Exception $e) {
            throw new Exception('An error occurred while generating the startup image. Please check the image, try to send it again and try again.');
        }

    }

    /**
     * @throws \Siberian\Exception
     */
    protected function replaceBundleId ()
    {
        // com.appsmobilecompany.base from pbxproj
        $this->__replace(
            [$this->_default_bundle_name => $this->_package_name],
            $this->_dest_source_amc . '/../AppsMobileCompany.xcodeproj/project.pbxproj');
    }

    protected function buildPList()
    {
        $plistPath = $this->_dest_source_amc . '/AppsMobileCompany-Info.plist';
        $device = $this->getDevice();

        $plist = new PListEditor\PListEditor();
        $plist->readFile($plistPath);
        // Preview PList

        $root = $plist->root();
        $root->removeProperty('CFBundleDisplayName');
        $root->addProperty(\PListEditor\PListProperty::PL_STRING,
            str_replace("&", '-', $this->_application_name), 'CFBundleDisplayName');

        $root->removeProperty('CFBundleIdentifier');
        $root->addProperty(\PListEditor\PListProperty::PL_STRING, $this->_package_name, 'CFBundleIdentifier');

        // Temporary fix for NFCReader
        $root->removeProperty('NFCReaderUsageDescription');

        // Version!
        $valueCFBundleVersion = null;
        if (isset($this->_previewer)) {
            $this->_previewer->setIosBuildNumber($this->_previewer->getIosBuildNumber() + 1)->save();
            $valueCFBundleVersion = $this->_previewer->getIosVersion() . '.' . $this->_previewer->getIosBuildNumber();
        } else {
            $device->setBuildNumber($device->getBuildNumber() + 1)->save();
            $valueCFBundleVersion = $device->getVersion() . '.' . $device->getBuildNumber();
        }

        $root->removeProperty('CFBundleVersion');
        $root->addProperty(\PListEditor\PListProperty::PL_STRING, $valueCFBundleVersion, 'CFBundleVersion');

        // Short version!
        $valueCFBundleShortVersionString = null;
        if (isset($this->_previewer)) {
            $valueCFBundleShortVersionString = $this->_previewer->getIosVersion();
        } else {
            $valueCFBundleShortVersionString = $device->getVersion();
        }

        $root->removeProperty('CFBundleShortVersionString');
        $root->addProperty(\PListEditor\PListProperty::PL_STRING, $valueCFBundleShortVersionString, 'CFBundleShortVersionString');

        // Status bar!
        $valueUIStatusBarHidden = $this->currentApplication->getIosStatusBarIsHidden() ?
            \PListEditor\PListProperty::PL_TRUE : \PListEditor\PListProperty::PL_FALSE;
        $root->removeProperty('UIStatusBarHidden');
        $root->addProperty($valueUIStatusBarHidden, null, 'UIStatusBarHidden');

        $root->removeProperty('UIViewControllerBasedStatusBarAppearance');
        $root->addProperty($valueUIStatusBarHidden, null, 'UIViewControllerBasedStatusBarAppearance');

        // iPhone X UILaunchStoryboardName
        $root->removeProperty('UILaunchStoryboardName');
        $root->addProperty(\PListEditor\PListProperty::PL_STRING, 'CDVLaunchScreen', 'UILaunchStoryboardName');

        // NS*Descriptions!
        $NSDescriptions = [
            'NSCameraUsageDescription' => 'ns_camera_ud',
            'NSBluetoothAlwaysUsageDescription' => 'ns_bluetooth_always_ud',
            'NSBluetoothPeripheralUsageDescription' => 'ns_bluetooth_peripheral_ud',
            'NSPhotoLibraryUsageDescription' => 'ns_photo_library_ud',
            'NSLocationWhenInUseUsageDescription' => 'ns_location_when_in_use_ud',
            'NSLocationAlwaysUsageDescription' => 'ns_location_always_ud',
            'NSLocationAlwaysAndWhenInUseUsageDescription' => 'ns_location_always_and_when_in_use_ud',
            'NSMotionUsageDescription' => 'ns_motion_ud',
        ];

        // If ads are available
        if ($this->withAds ||
            $this->currentApplication->getRequestTrackingAuthorization()) {
            $NSDescriptions['NSUserTrackingUsageDescription'] = 'ns_user_tracking_ud';
        }

        foreach ($NSDescriptions as $key => $NSDescription) {
            // Placeholders!
            $dataString = str_replace('#APP_NAME', $this->_application_name, $device->getData($NSDescription));

            $root->removeProperty($key);
            $root->addProperty(\PListEditor\PListProperty::PL_STRING, $dataString, $key);
        }

        // Orientation!
        $iPhone = [];
        $iPad = [];
        $orientations = Siberian_Json::decode($device->getOrientations());
        foreach ($orientations as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'iphone-portrait':
                        $iPhone[] = 'UIInterfaceOrientationPortrait';
                        break;
                    case 'iphone-upside-down':
                        $iPhone[] = 'UIInterfaceOrientationPortraitUpsideDown';
                        break;
                    case 'iphone-landscape-left':
                        $iPhone[] = 'UIInterfaceOrientationLandscapeLeft';
                        break;
                    case 'iphone-landscape-right':
                        $iPhone[] = 'UIInterfaceOrientationLandscapeRight';
                        break;
                    case 'ipad-portrait':
                        $iPad[] = 'UIInterfaceOrientationPortrait';
                        break;
                    case 'ipad-upside-down':
                        $iPad[] = 'UIInterfaceOrientationPortraitUpsideDown';
                        break;
                    case 'ipad-landscape-left':
                        $iPad[] = 'UIInterfaceOrientationLandscapeLeft';
                        break;
                    case 'ipad-landscape-right':
                        $iPad[] = 'UIInterfaceOrientationLandscapeRight';
                        break;
                }
            }
        }

        // iPhone!
        $root->removeProperty('UISupportedInterfaceOrientations');
        $root->addProperty(\PListEditor\PListProperty::PL_ARRAY, $iPhone, 'UISupportedInterfaceOrientations');

        // iPad!
        $root->removeProperty('UISupportedInterfaceOrientations~ipad');
        $root->addProperty(\PListEditor\PListProperty::PL_ARRAY, $iPad, 'UISupportedInterfaceOrientations~ipad');

        // AdMob SDK
        if ($this->withAds) {
            $root->removeProperty('GADApplicationIdentifier');
            $root->addProperty(\PListEditor\PListProperty::PL_STRING, $this->admobAppIdentifier, 'GADApplicationIdentifier');

            // com.appsmobilecompany.base from pbxproj
            $this->__replace(
                ['ca-app-pub-0000000000000000~0000000000' => $this->admobAppIdentifier],
                $this->_dest_source_amc . '/../AppsMobileCompany/config.xml');
        }

        $plist->save();

        return $this;
    }
}
