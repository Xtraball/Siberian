<?php

abstract class Template_View_Block_Abstract extends Core_View_Default {

    protected $_block;
    protected $_overview;

    public function setBlock($block) {
        $this->_block = $block;
        return $this;
    }

    public function getBlock() {
        return $this->_block;
    }

    public function setOverview($overview) {
        $this->_overview = $overview;
        return $this;
    }

    public function getOverview() {
        return $this->_overview;
    }
}



?>

