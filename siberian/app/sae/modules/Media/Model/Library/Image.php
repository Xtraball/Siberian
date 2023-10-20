<?php

use Core\Model\Base;

/**
 * Class Media_Model_Library_Image
 *
 * @method Media_Model_Db_Table_Library_Image getTable()
 * @method boolean getCanBeColorized()
 */
class Media_Model_Library_Image extends Base
{

    const PATH = '/images/library';
    const APPLICATION_PATH = '/images/application/%d/icons';

    /**
     * @var string
     */
    protected $_db_table = Media_Model_Db_Table_Library_Image::class;

    /**
     * @param string $path
     * @param null $appId
     * @return string
     */
    public static function getImagePathTo($path = '', $appId = null): string
    {
        return self::_getImagePath($path, $appId, false);
    }

    /**
     * @param string $path
     * @param null $appId
     * @return string
     */
    public static function getBaseImagePathTo($path = '', $appId = null): string
    {
        return self::_getImagePath($path, $appId, true);
    }

    /**
     * @param string $path
     * @param null $appId
     * @param bool $base
     * @return string
     */
    public static function _getImagePath($path = '', $appId = null, $base = true): string
    {
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }

        if (!is_null($appId)) {
            $path = sprintf(self::APPLICATION_PATH . $path, $appId);
        } else if (strpos($path, '/app') === 0) {
            # Do nothing for /app/* from modules
        } else {
            $path = self::PATH . $path;
        }

        return $base ? path($path) : rpath($path);
    }

    /**
     * The params are prefixed with __ to avoid conflict with internal params.
     *
     * @deprecated will be deprecated in 4.2.x `
     *
     * @param string $__url
     * @param array $__params
     * @param null $__locale
     * @return string
     */
    public function getUrl($__url = '', array $__params = array(), $__locale = null)
    {
        return $this->getRelativePath();
    }

    /**
     * @return string
     */
    public function getRelativePath(): string
    {
        if ($this->getLink()) {
            $url = self::getImagePathTo($this->getLink(), $this->getAppId());
            $baseUrl = self::getBaseImagePathTo($this->getLink(), $this->getAppId());
            if (is_file($baseUrl)) {
                return $url;
            }
        }

        return $this->getNoImage();
    }

    /**
     * @return bool
     */
    public function checkFile(): bool
    {
        if ($this->getLink()) {
            $baseUrl = self::getBaseImagePathTo($this->getLink(), $this->getAppId());
            return is_file($baseUrl);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getNoImage()
    {
        return '/app/sae/design/desktop/flat/images/placeholder/blank-feature-icon.png';
    }

    public function getSecondaryUrl()
    {
        $url = '';
        if ($this->getSecondaryLink()) {
            $url = self::getImagePathTo($this->getSecondaryLink());
            if (!file_exists(self::getBaseImagePathTo($this->getSecondaryLink()))) $url = '';
        }

        if (empty($url)) {
            $url = $this->getNoImage();
        }

        return $url;

    }

    public function getThumbnailUrl()
    {
        $url = '';
        if ($this->getThumbnail()) {
            $url = self::getImagePathTo($this->getThumbnail());
            if (!file_exists(self::getBaseImagePathTo($this->getThumbnail()))) $url = '';
        }

        if (empty($url)) {
            $url = $this->getUrl();
        }

        return $url;
    }

    public function updatePositions($positions)
    {
        $this->getTable()->updatePositions($positions);

        return $this;
    }

    /**
     * Keywords for icon library filters
     * @return string
     */
    public function getFilterKeywords()
    {
        $link = $this->getLink();

        // Link must be filtered off from regular words
        $link = str_replace([
            'app/',
            'sae/',
            'mae/',
            'pe/',
            'local/',
            'modules/',
            'resources/',
            'features/',
            'icons/',
            'media/',
            'library/',
            '.png',
            '.jpg',
            '.jpeg',
            '.bmp',
            '.gif',
        ], '', $link);
        $link = str_replace('/', ',', $link);
        $link = preg_replace("/\d/", '', $link); // Also replace numbers
        $link = strtolower(trim(preg_replace("/,+/", ',', $link), ','));

        $keywords = $link . ',' . $this->getKeywords();

        $list = explode(',', $keywords);
        $list = array_keys(array_flip($list));

        // Automatically adds keywords to the translation file
        foreach ($list as $l) {
            extract_p__('keywords', $l, null, true);
        }

        $withTranslation = $list;
        foreach ($list as $l) {
            $withTranslation[] = strtolower(p__('keywords', $l));
        }
        // Again, remove dupes*
        $withTranslation = array_keys(array_flip($withTranslation));

        return implode_polyfill(',', $withTranslation);
    }
}
