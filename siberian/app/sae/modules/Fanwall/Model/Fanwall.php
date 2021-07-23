<?php

namespace Fanwall\Model;

use Core\Model\Base;
use Siberian\Image;
use Zend_Exception;

/**
 * Class Fanwall
 * @package Fanwall\Model
 *
 * @method string getAdminEmails()
 * @method string getIconTopics()
 * @method string getIconNearby()
 * @method string getIconMap()
 * @method string getIconGallery()
 * @method string getIconPost()
 */
class Fanwall extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\Fanwall::class;

    /**
     * @return array
     */
    public function buildSettings ()
    {
        $settings = [
            'icons' => [],
        ];
        $icons = [
            'post' => $this->getIconPost(),
            'nearby' => $this->getIconNearby(),
            'map' => $this->getIconMap(),
            'gallery' => $this->getIconGallery(),
            'new' => $this->getIconNew(),
            'profile' => $this->getIconProfile(),
        ];
        foreach ($icons as $key => $path) {
            $iconPath = path("/images/application{$path}");
            if (is_file($iconPath)) {
                $settings['icons'][$key] = (new Image($iconPath))->resize(32, 32)->inline('png', 100);
            } else {
                $settings['icons'][$key] = null;
            }
        }

        $settings['enable_moderation'] = (boolean) $this->getEnableModeration();
        $settings['max_images'] = (integer) $this->getMaxImages();
        $settings['cardDesign'] = ($this->getDesign() === 'card');
        $settings['photoMode'] = $this->getPhotoMode();
        $settings['photoPosition'] = $this->getPhotoPosition();
        $settings['maxBodySize'] = (integer)$this->getMaxBodySize();
        $settings['features'] = [
            'enableNearby' => (boolean) $this->getEnableNearby(),
            'enableMap' => (boolean) $this->getEnableMap(),
            'enableGallery' => (boolean) $this->getEnableGallery(),
            'enableUserLike' => (boolean) $this->getEnableUserLike(),
            'enableUserPost' => (boolean) $this->getEnableUserPost(),
            'enableUserComment' => (boolean) $this->getEnableUserComment(),
            'enableUserShare' => (string) $this->getEnableUserShare(),
        ];

        return $settings;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id)
    {

        $in_app_states = [
            [
                "state" => "fanwall-home",
                "offline" => false,
                "params" => [
                    "value_id" => $value_id,
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * GET Feature url for app init
     *
     * @param $optionValue
     * @return array
     */
    public function getAppInitUris ($optionValue)
    {
        $featureUrl = __url("/fanwall/mobile_home/index", [
            "value_id" => $this->getValueId(),
        ]);
        $featurePath = __path("/fanwall/mobile_home/index", [
            "value_id" => $this->getValueId(),
        ]);


        return [
            "featureUrl" => $featureUrl,
            "featurePath" => $featurePath,
        ];
    }

    /**
     * @return int
     */
    public function getPendingActions ($optionValue): int
    {
        $approval = (new Approval())->findAll(['value_id = ?' => $optionValue->getId()]);

        return $approval->count();
    }

    /**
     * @param null $optionValue
     * @return array|bool
     * @throws Zend_Exception
     */
    public function getEmbedPayload($optionValue = null)
    {
        $fanWall = (new self())->find($optionValue->getId(), "value_id");

        return [
            "settings" => $fanWall->buildSettings()
        ];
    }

}
