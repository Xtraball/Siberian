<?php

use Siberian\Xss;

/**
 * Class Cms_Model_Application_Page_Block_Source
 */
class Cms_Model_Application_Page_Block_Source extends Cms_Model_Application_Page_Block_Abstract
{

    /**
     * @var string
     */
    protected $_db_table = Cms_Model_Db_Table_Application_Page_Block_Source::class;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->getSource() &&
            $this->getHeight()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return $this|Cms_Model_Application_Page_Block_Abstract
     * @throws Zend_Exception
     */
    public function populate($data = [])
    {
        // Sanitize HTML/Source code
        $original = $data['source'];
        $source = <<<RAW
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <meta content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0" name="viewport" />
        <meta content="black" name="apple-mobile-web-app-status-bar-style" />
        <meta content="IE=8" http-equiv="X-UA-Compatible" />
        
        <style type="text/css">
            html, body { margin: 0; padding: 0; border: none; }
            html { overflow: hidden; }
            body { background-color:white; }
        </style>
    </head>
<body>
    {$data['source']}
</body>
<script type="text/javascript">
var inAppLinks = document.querySelectorAll("a[data-state]");
[].forEach.call(inAppLinks, function(el) {
    el.href = "javascript:void(0);";
    el.addEventListener("click", function() {
        parent.postMessage("state-go=state:"+el.attributes["data-state"].value+",offline:"+el.attributes["data-offline"].value+","+el.attributes["data-params"].value+"", "*");
    });
});
</script>
</html>
RAW;

        $this
            ->setSource($source)
            ->setOriginal($original)
            ->setHeight($data['height'])
            ->setUnit($data['unit']);

        return $this;
    }

    /**
     * @param $source
     * @return self
     */
    public function setSource($source): self
    {
        return $this->setData('source', base64_encode($source));
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        $source = $this->getData('source');
        return empty($source) ? '' :
            base64_decode($source);
    }

    /**
     * @param $original
     * @return self
     */
    public function setOriginal($original): self
    {
        return $this->setData('original', base64_encode($original));
    }

    /**
     * @return string
     */
    public function getOriginal(): string
    {
        $original = $this->getData('original');
        return empty($original) ? '' :
            base64_decode($original);
    }

}