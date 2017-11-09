<?php

abstract class Cms_Model_Application_Page_Block_Image_Abstract extends Cms_Model_Application_Page_Block_Abstract {

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = array()) {
        $library_model = new Cms_Model_Application_Page_Block_Image_Library();
        # Create libraries
        $library_last_id = $library_model->findLastLibrary();
        foreach($data["images"] as $image) {
            $lib_image = new Cms_Model_Application_Page_Block_Image_Library();
            $path = Siberian_Feature::saveImageForOption($this->option_value, $image);

            $lib_image
                ->setImageFullsizeUrl($path)
                ->setImageUrl($path)
                ->setLibraryId($library_last_id)
                ->save()
            ;
        }

        $this
            ->setDescription($data["description"])
            ->setLibraryId($library_last_id)
        ;

        return $this;
    }
}
