<?php

class Core_View_Email_Default extends Core_View_Default
{

    /** @migration
     * @todo use good path relative to the good edition/inheritance */
    public function getImage($name) {
        return $this->getRequest()->getMediaUrl().'/app/sae/design/email/images/' . $name;
    }

    public function getJs($name) {
        return $this->getRequest()->getMediaUrl().'/app/sae/design/email/js/' . $name;
    }

}