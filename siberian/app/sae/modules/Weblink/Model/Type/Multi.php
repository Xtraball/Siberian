<?php
class Weblink_Model_Type_Multi extends Weblink_Model_Weblink {

    protected $_is_cacheable = true;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_type_id = 2;
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "links-view",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    public function addLinks() {
        $link = new Weblink_Model_Weblink_Link();
        $links = $link->findAll(array('weblink_id' => $this->getId()));
        $this->setLinks($links);
        return $this;
    }

    public function getCoverUrl() {
        $cover_path = Application_Model_Application::getImagePath().$this->getCover();
        $cover_base_path = Application_Model_Application::getBaseImagePath().$this->getCover();
        if($this->getCover() AND file_exists($cover_base_path)) {
            return $cover_path;
        }
        return null;
    }

    public function getFeaturePaths($option_value) {
        if(!$this->isCacheable()) return array();

        $paths = array();

        $paths[] = $option_value->getPath("weblink/mobile_multi/find", array('value_id' => $option_value->getId()), false);

        return $paths;
    }

    public function getAssetsPaths($option_value) {
        if(!$this->isCacheable()) return array();

        $paths = array();

        $paths[] = $this->getCoverUrl();

        return $paths;
    }

    public function createDummyContents($option_value, $design, $category) {

        $dummy_content_xml = $this->_getDummyXml($design, $category, $option_value, $option_value);

        foreach ($dummy_content_xml->children() as $content) {

            if($content->attributes()->type_id == 2) {

                $this->unsData();

                $this->setValueId($option_value->getId())
                    ->setCover((string) $content->cover)
                    ->save()
                ;

                $i = 0;
                foreach ($content->links as $links) {

                    foreach ($links as $key => $value) {
                        $data = array(
                            "weblink_id" => $this->getId(),
                            "title" => (string)$value->title,
                            "url" => (string)$value->url,
                            "picto" => (string)$value->picto,
                            "position" => $i++,
                        );

                        $link = new Weblink_Model_Weblink_Link();

                        $link->setData($data)
                            ->save()
                        ;
                    }
                }
            }
        }
    }

    public function copyTo($option) {

        $old_weblink_id = $this->getId();

        $this->setId(null)->setValueId($option->getId());

        if($image_url = $this->getCoverUrl()) {

            $file = pathinfo($image_url);
            $filename = $file['basename'];

            $relativePath = $option->getRelativePath();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE.'/'.$relativePath);

            if(!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image_url);
            $img_dst = $folder.'/'.$filename;

            if(copy($img_src, $img_dst)) {
                $this->setImage($relativePath.'/'.$filename);
            }
        }

        $this->save();

        $link = new Weblink_Model_Weblink_Link();
        $links = $link->findAll(array('weblink_id' => $old_weblink_id));
        foreach($links as $link) {
            $link->setId(null)->setWeblinkId($this->getId())->save();
        }

        return $this;
    }
    
}
