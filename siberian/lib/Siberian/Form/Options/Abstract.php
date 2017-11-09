<?php
/**
 * Class Siberian_Form_Options_Abstract
 */
abstract class Siberian_Form_Options_Abstract extends Siberian_Form_Abstract {

    /**
     * @var string
     */
    public $color = "color-red";

    protected $layout = null;

    /**
     * Siberian_Form_Options_Abstract constructor.
     * @param mixed|null $options
     */
    public function __construct($options) {
        if($options instanceof Application_Model_Layout_Homepage) {
            $this->layout = $options;
            $options = null;
        }

        parent::__construct($options);
    }

    public function init(){
        $this->setIsFormHorizontal(false);

        parent::init();

        $this
            ->setAction(__path("/application/customization_design_style/formoptions"))
            ->setAttrib("id", "form-options")
        ;

    }

    /**
     * Special hook for Siberian design
     */
    public function siberianDesign() {
        //$this->removeElement("layout_visibility");
        //$this->removeElement("homepage_slider_is_visible");
    }
}