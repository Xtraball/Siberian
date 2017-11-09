<?php
class Weblink_Model_Type_Mono extends Weblink_Model_Weblink {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_type_id = 1;
        return $this;
    }

    public function save() {

        if(!$this->getId()) $this->setTypeId($this->_type_id);
        parent::save();
        if(!$this->getIsDeleted()) {
            if(!$this->getLink()->getId()) $this->getLink()->setWeblinkId($this->getId());
            $this->getLink()->save();
        }
        return $this;
    }

    public function addLinks() {
        $link = new Weblink_Model_Weblink_Link();
        if($this->getId()) {
            $link->find($this->getId(), 'weblink_id');
        }
        $this->setLink($link);
        return $this;
    }

    public function createDummyContents($option_value, $design, $category) {

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        foreach ($dummy_content_xml->children() as $content) {

            if($content->attributes()->type_id == 1) {

                $link = new Weblink_Model_Weblink_Link();
                $link->setUrl((string) $content->url);


                $this->setValueId($option_value->getId())
                    ->setLink($link)
                    ->save()
                ;

            }
        }
    }

    public function copyTo($option) {
        $old_weblink_id = $this->getId();
        $this->setId(null)->setValueId($option->getId())->save();

        $link = new Weblink_Model_Weblink_Link();
        $links = $link->findAll(array('weblink_id' => $old_weblink_id));
        foreach($links as $link) {
            $link->setId(null)->setWeblinkId($this->getId())->save();
        }

        return $this;
    }

}
