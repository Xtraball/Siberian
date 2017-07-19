<?php
class Sourcecode_Model_Sourcecode extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Sourcecode_Model_Db_Table_Sourcecode';
        return $this;
    }

    public function save() {
        parent::save();
        $this->cleanHtmlFile();
        return $this;
    }

    /**
     * @return string
     */
    public function availableOffline() {
        return "partial";
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "sourcecode-view",
                "offline" => $this->isCacheable(),
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @param $option_value
     * @return bool
     */
    public function getEmbedPayload($option_value) {

        $payload = false;

        if($this->getId()) {
            $payload = array(
                "sourcecode" => array(
                    "id"   => $this->getId(),
                    "code" => $this->getHtmlFileCode()
                ),
                "page_title" => $option_value->getTabbarName()
            );
        }

        return $payload;

    }

    public function getHtmlFilePath($full_path = false) {

        if(!file_exists(Core_Model_Directory::getCacheDirectory(true).'/'.$this->_getFilename())) {
            $file = fopen(Core_Model_Directory::getCacheDirectory(true).'/'.$this->_getFilename(), 'w');

            $iframe_style_scroll = "";
            $html_code = $this->getHtmlCode();
            $html_a_target = "_top";
            if($this->getSession()->isOverview) {
                $html_a_target = "_blank";
            }

            $doc = new Dom_SmartDOMDocument();
            $doc->loadHTML($html_code);

            //detect if the code have an iframe
            if($doc->getElementsByTagName('iframe')->length != 0) {
                $iframe_style_scroll = "-webkit-overflow-scrolling:touch";
            }

            $links = $doc->getElementsByTagName('a');
            foreach ($links as $item) {
                $item->setAttribute('target', $html_a_target);
            }
            $html_code = html_entity_decode($doc->saveHTML(), ENT_QUOTES, "UTF-8");

            $html = '
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <meta content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0" name="viewport" />
        <meta content="black" name="apple-mobile-web-app-status-bar-style" />
        <meta content="IE=8" http-equiv="X-UA-Compatible" />
        
        <style type="text/css">
            html, body { margin:0; padding:0; border:none; }
            html { overflow: scroll; ' . $iframe_style_scroll . ' }
            body { font-size: 15px; width: 100%; height: 100%; overflow: auto; -webkit-user-select : none; -webkit-text-size-adjust : none; -webkit-touch-callout: none; line-height:1; background-color:white; }
        </style>
    </head>
<body>
    '.$html_code.'
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
</html>';

            fputs($file, $html);
            fclose($file);
        }

        return Core_Model_Directory::getCacheDirectory($full_path).'/'.$this->_getFilename();

    }

    public function getHtmlFileCode() {
        $file = @file_get_contents($this->getHtmlFilePath(true));

        return is_string($file) ? $file : "";
    }


    public function cleanHtmlFile() {
        if(file_exists(Core_Model_Directory::getCacheDirectory(true).'/'.$this->_getFilename())) {
            @unlink(Core_Model_Directory::getCacheDirectory(true).'/'.$this->_getFilename());
        }
        return $this;
    }

    protected function _getFilename() {
        $key = md5($this->getUpdatedAt()) . (int) $this->getSession()->isOverview;
        return 'html_content_'.$key.'_'.$this->getId().'.html';
    }

    public function getFeaturePaths($option_value) {
        if(!$this->isCacheable()) return array();


        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";

        if(!$paths = $this->cache->load($cache_id)) {
            $paths = array();
            $paths[] = $option_value->getPath("sourcecode/mobile_view/find", array('value_id' => $option_value->getId()), false);

            $this->cache->save($paths, $cache_id, array(
                "feature_paths",
                "feature_paths_valueid_{$value_id}"
            ));
        }

        return $paths;
    }


    public function getAssetsPaths($option_value) {
        if(!$this->isCacheable()) return array();


        $value_id = $option_value->getId();
        $cache_id = "assets_paths_valueid_{$value_id}";
        if(!$paths = $this->cache->load($cache_id)) {

            $paths = array();

            $matches = array();
            $regex_url = "/((?:http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\s\"]*)\.(?:png|gif|jpeg|jpg|svg|css|js)+)+/";
            preg_match_all($regex_url, $this->getHtmlFileCode(), $matches);

            $matches = call_user_func_array('array_merge', $matches);

            if($matches && count($matches) > 1) {
                unset($matches[0]);
                $paths = array_merge($paths, $matches);
            }

            $this->cache->save($paths, $cache_id, array(
                "assets_paths",
                "assets_paths_valueid_{$value_id}"
            ));
        }

        return $paths;
    }

    public function isCacheable() {
        return !!$this->getAllowOffline();
    }

    /**
     * @param $option
     * @return $this
     */
    public function copyTo($option) {

        # Remove in-app links
        $content = $this->getHtmlCode();
        $content = preg_replace('/<a(.*)data-state=(.*)>(.*)<\/a>/mi', '', $content);
        $this->setHtmlCode($content);

        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null) {
        if($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $sourcecode_model = new Sourcecode_Model_Sourcecode();
            $sourcecode = $sourcecode_model->find($value_id, "value_id");

            $dataset = array(
                "option" => $current_option->forYaml(),
                "sourcecode" => $sourcecode->getData(),
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
                throw new Exception("#089-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#089-01: Unable to export the feature, non-existing id.");
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function importAction($path) {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch(Exception $e) {
            throw new Exception("#089-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if(isset($dataset["option"])) {
            $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            if(isset($dataset["sourcecode"])) {
                $new_sourcecode = new Sourcecode_Model_Sourcecode();
                $new_sourcecode
                    ->setData($dataset["sourcecode"])
                    ->setData("value_id", $application_option->getId())
                    ->unsData("id")
                    ->unsData("source_code_id")
                    ->save()
                ;
            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }
}
