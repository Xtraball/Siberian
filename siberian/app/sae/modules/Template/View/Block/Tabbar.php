<?php

class Template_View_Block_Tabbar extends Template_View_Block_Abstract {

    public function resetTemplate() {
        $this->setTemplate('template/block/tabbar.phtml');
        return $this;
    }

}
