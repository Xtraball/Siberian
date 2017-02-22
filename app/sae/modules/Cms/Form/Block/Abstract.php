<?php

/**
 * Class Cms_Form_Block_Abstract
 */
abstract class Cms_Form_Block_Abstract extends Siberian_Form_Abstract {

    /**
     * @var null|string
     */
    public $uniqid = null;

    /**
     * @var bool
     */
    public $required = false;

    /**
     * Cms_Form_Block_Abstract constructor.
     * @param null $options
     */
    public function __construct($options = null) {

        $this->uniqid = uniqid();

        parent::__construct($options);
    }

    /**
     * @return null|string
     */
    public function getUniqid() {
        return $this->uniqid;
    }

    /**
     * @param $required
     * @return $this
     */
    public function setRequired($required) {
        $this->required = $required;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRequired() {
        return $this->required;
    }

}