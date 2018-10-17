<?php

/**
 * Class Core_View_Email_Default
 */
class Core_View_Email_Default extends Core_View_Default
{
    /**
     * @param $name
     * @param bool $base
     * @return bool|string
     */
    public function getImage($name, $base = false)
    {
        return $this->getRequest()->getMediaUrl() . '/app/sae/design/email/images/' . $name;
    }

    /**
     * @param $name
     * @return string
     */
    public function getJs($name)
    {
        return $this->getRequest()->getMediaUrl() . '/app/sae/design/email/js/' . $name;
    }

}