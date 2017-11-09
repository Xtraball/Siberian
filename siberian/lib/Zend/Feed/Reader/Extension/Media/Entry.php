<?php

class Zend_Feed_Reader_Extension_Media_Entry extends Zend_Feed_Reader_Extension_EntryAbstract {

    public function getThumbnails() {

        if(isset($this->_data['thumbnails'])){
            return $this->_data['thumbnails'];
        }

        $thumbnail_list = $this->_xpath->evaluate(
            $this->getXpathPrefix() . '/media:content/media:thumbnail'
        );

        $thumbnails = array();

        foreach($thumbnail_list as $_thumbnail_element) {
            array_push($thumbnails, array(
                'url'    => $_thumbnail_element->getAttribute('url'),
                'width'  => $_thumbnail_element->getAttribute('width'),
                'height' => $_thumbnail_element->getAttribute('height'),
            ));
        }

        if(!count($thumbnails)){
            $thumbnails = null;
        }

        $this->_data['thumbnails'] = $thumbnails;

        return $this->_data['thumbnails'];
    }

    protected function _registerNamespaces() {
        $this->_xpath->registerNamespace('media', 'http://search.yahoo.com/mrss');
    }
}