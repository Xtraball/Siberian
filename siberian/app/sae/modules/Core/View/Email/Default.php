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
        if (!$this->getRequest()) {
            $baseUrl = sprintf('%s://%s',
                __get('use_https') ? 'https' : 'http',
                __get('main_domain'));
        } else {
            $baseUrl = $this->getRequest()->getMediaUrl();
        }
        return $baseUrl . '/app/sae/design/email/images/' . $name;
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