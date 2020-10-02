<?php

/**
 * Class Media_Model_Gallery_Image_Custom
 *
 * @method integer getId()
 * @method setGalleryId(integer $galleryId)
 * @method setUrl(string $url)
 * @method $this setTitle(string $title)
 * @method $this setDescription(string $description)
 */
class Media_Model_Gallery_Image_Custom extends Media_Model_Gallery_Image_Abstract
{
    /**
     * @var string
     */
    protected $_db_table = Media_Model_Db_Table_Gallery_Image_Custom::class;

    /**
     * @return array
     */
    public function getAllTypes()
    {
        $types = [];
        foreach ($this->_flux as $k => $flux) {
            $types[$k] = new Core_Model_Default([
                'code' => $k,
                'url' => $flux,
                'label' => $this->_labels[$k]
            ]);
        }
        return $types;
    }

    /**
     * @param $offset
     * @param int $limit
     * @return array|mixed
     */
    public function getImages($offset, $limit = self::DISPLAYED_PER_PAGE)
    {

        try {
            $params = [];
            if (!empty($limit)) {
                $params['limit'] = $limit;
            }
            if (!empty($offset)) {
                $params['offset'] = $offset;
            }

            $images = (new Media_Model_Gallery_Image_Custom())->findAll([
                'gallery_id' => $this->getImageId()
            ], 'position ASC', $params);
        } catch (Exception $e) {
            $images = [];
        }

        $returnedImages = [];
        foreach ($images as $key => $image) {
            $returnedImages[] = new Core_Model_Default([
                'offset' => $offset++,
                'title' => $image->getTitle(),
                'description' => $image->getDescription(),
                'author' => null,
                'image' => Application_Model_Application::getImagePath() . $image->getData('url')
            ]);
        }

        return $returnedImages;
    }

}

