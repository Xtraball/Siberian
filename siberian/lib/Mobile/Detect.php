<?php

/**
 * Mobile Detect
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Mobile_Detect {

    protected $accept;
    protected $deviceName;
    public $userAgent;
    protected $isNative = null;
    protected $isPreviewer = null;
    protected $isMobile = false;
    protected $isAndroiddevice = null;
    protected $isAndroid = null;
    protected $isAndroidtablet = null;
    protected $isIosdevice = null;
    protected $isIphone = null;
    protected $isIpad = null;
    protected $isBlackberry = null;
    protected $isBlackberrytablet = null;
    protected $isOpera = null;
    protected $isPalm = null;
    protected $isWindows = null;
    protected $isGooglebot = null;
    protected $isWindowsphone = null;
    protected $isGeneric = null;
    protected $devices = array(
        "androiddevice" => "android",
        "android" => "android.*mobile",
        "androidtablet" => "android(?!.*mobile)",
        "blackberry" => "blackberry",
        "blackberrytablet" => "rim tablet os",
        "iosdevice" => "(iphone|ipod|ipad)",
        "iphone" => "(iphone|ipod)",
        "ipad" => "(ipad)",
        "palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
        "windows" => "windows ce; (iemobile|ppc|smartphone)",
        "windowsphone" => "windows phone os",
        "googlebot" => "googlebot",
        "native" => "siberian.application",
        "previewer" => "siberian.application.previewer",
        "generic" => "(kindle|mobile|mmp|midp|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)"
    );

    public function __construct() {

        $this->userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $this->accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

        if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            $this->isMobile = true;
        } elseif (strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos($this->accept, 'application/vnd.wap.xhtml+xml') > 0) {
            $this->isMobile = true;
        }
        foreach ($this->devices as $device => $regexp) {
            if ($this->isDevice($device)) {
                $this->isMobile = true;
                if(!$this->deviceName) $this->deviceName = $device;
            }
        }

    }

    /**
     * Overloads isAndroid() | isAndroidtablet() | isIphone() | isIpad() | isBlackberry() | isBlackberrytablet() | isPalm() | isWindowsphone() | isWindows() | isGeneric() through isDevice()
     *
     * @param string $name
     * @param array $arguments
     * @return bool
     */
    public function __call($name, $arguments) {
        $device = substr($name, 2);
        if ($name == "is" . ucfirst($device) && array_key_exists(strtolower($device), $this->devices)) {
            return $this->isDevice($device);
        } else {
            trigger_error("Method $name not defined", E_USER_WARNING);
        }
    }

    /**
     * Returns true if any type of mobile device detected, including special ones
     * @return bool
     */
    public function isMobile() {
        return $this->isMobile;
    }

    protected function isDevice($device) {
        $var = "is" . ucfirst($device);
        $return = $this->$var === null ? (bool) preg_match("/" . $this->devices[strtolower($device)] . "/i", $this->userAgent) : $this->$var;
        if ($device != 'generic' && $return == true) {
            $this->isGeneric = false;
        }

        return $return;
    }

    public function getDeviceName() {
        return $this->deviceName;
    }

}
