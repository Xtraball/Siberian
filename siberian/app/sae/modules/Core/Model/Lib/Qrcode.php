<?php

class Core_Model_Lib_Qrcode {

    protected $_template = '
        <div class="qrcode">
            <img src="%s" alt="%s" border="0" />
        </div>
        ';

    /**
     * Generate qrCode.
     *
     * @return string
     */
    public function getImage($name, $text, $params = array())
    {

        $size = !empty($params['size']) ? $params['size'] : "100x100";

        $default_params = array(
            'text'       => $text,
            'size'       => $size,
            'correction' => 'M',
            'margin'     => 0
        );
        $params = array_merge($default_params, $params);

        $params['text']   = urlencode($params['text']);
        $params['margin'] = (int)$params['margin'];
        if (!in_array($params['correction'], array('L', 'M', 'Q', 'H'))) {
            $params['correction'] = 'M';
        }
        if (!preg_match('/^\d+x\d+$/', $params['size'])) {
            $params['size'] = '100x100';
        }

        $url = "https://chart.apis.google.com/chart?cht=qr&chl={$params['text']}"
             . "&chld={$params['correction']}%7C{$params['margin']}"
             . "&chs={$params['size']}";

        if(!empty($params['without_template'])) {
            return $url;
        }
        else {
            return sprintf($this->_template, $url, $name);
        }

    }

    public function getImageWithoutTemplate($name, $text, $params = array())
    {

        $size = !empty($params['size']) ? $params['size'] : "100x100";

        $default_params = array(
            'text'       => $text,
            'size'       => $size,
            'correction' => 'M',
            'margin'     => 0
        );
        $params = array_merge($default_params, $params);

        $params['text']   = urlencode($params['text']);
        $params['margin'] = (int)$params['margin'];
        if (!in_array($params['correction'], array('L', 'M', 'Q', 'H'))) {
            $params['correction'] = 'M';
        }
        if (!preg_match('/^\d+x\d+$/', $params['size'])) {
            $params['size'] = '100x100';
        }

        return "https://chart.apis.google.com/chart?cht=qr&chl={$params['text']}"
             . "&chld={$params['correction']}|{$params['margin']}"
             . "&chs={$params['size']}";

    }

}
