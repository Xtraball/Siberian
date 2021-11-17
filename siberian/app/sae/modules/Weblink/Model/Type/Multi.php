<?php

/**
 * Class Weblink_Model_Type_Multi
 */
class Weblink_Model_Type_Multi extends Weblink_Model_Weblink
{

    /**
     * @var bool
     */
    protected $_is_cacheable = true;

    /**
     * Weblink_Model_Type_Multi constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_type_id = 2;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id)
    {

        $in_app_states = [
            [
                'state' => 'links-view',
                'offline' => false,
                'params' => [
                    'value_id' => $value_id,
                ],
            ],
        ];

        return $in_app_states;
    }

    /**
     * @return $this|void
     */
    public function addLinks()
    {
        $link = new Weblink_Model_Weblink_Link();
        $links = $link->findAll(
            [
                'weblink_id' => $this->getId(),
            ],
            [
                'position ASC',
            ]
        );
        $this->setLinks($links);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCoverUrl()
    {
        $coverPath = Application_Model_Application::getImagePath() . $this->getCover();
        $coverBasePath = Application_Model_Application::getBaseImagePath() . $this->getCover();
        if ($this->getCover() && file_exists($coverBasePath)) {
            return $coverPath;
        }
        return null;
    }

    /**
     * @param $option_value
     * @return array
     */
    public function getFeaturePaths($option_value)
    {
        if (!$this->isCacheable()) {
            return [];
        }

        $paths = [];

        $paths[] = $option_value->getPath('weblink/mobile_multi/find', ['value_id' => $option_value->getId()], false);

        return $paths;
    }

    /**
     * @param $option_value
     * @return array
     */
    public function getAssetsPaths($option_value)
    {
        if (!$this->isCacheable()) {
            return [];
        }

        $paths = [];

        $paths[] = $this->getCoverUrl();

        return $paths;
    }

    /**
     * @param $option_value
     * @param $design
     * @param $category
     */
    public function createDummyContents($option_value, $design, $category)
    {

        $dummy_content_xml = $this->_getDummyXml($design, $category, $option_value, $option_value);

        // Continue if dummy is empty!
        if (!$dummy_content_xml) {
            return;
        }

        foreach ($dummy_content_xml->children() as $content) {

            if ($content->attributes()->type_id == 2) {

                $this->unsData();

                $this->setValueId($option_value->getId())
                    ->setCover((string)$content->cover)
                    ->save();

                $i = 0;
                foreach ($content->links as $links) {

                    foreach ($links as $key => $value) {
                        $data = [
                            "weblink_id" => $this->getId(),
                            "title" => (string)$value->title,
                            "url" => (string)$value->url,
                            "picto" => (string)$value->picto,
                            "position" => $i++,
                        ];

                        $link = new Weblink_Model_Weblink_Link();

                        $link->setData($data)
                            ->save();
                    }
                }
            }
        }
    }

    /**
     * @param $option
     * @return $this
     */
    public function copyTo($option, $parent_id = null)
    {

        $old_weblink_id = $this->getId();

        $this->setId(null)->setValueId($option->getId());

        if ($image_url = $this->getCoverUrl()) {

            $file = pathinfo($image_url);
            $filename = $file['basename'];

            $relativePath = $option->getRelativePath();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE . '/' . $relativePath);

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }

            $img_src = Core_Model_Directory::getBasePathTo($image_url);
            $img_dst = $folder . '/' . $filename;

            if (copy($img_src, $img_dst)) {
                $this->setImage($relativePath . '/' . $filename);
            }
        }

        $this->save();

        $link = new Weblink_Model_Weblink_Link();
        $links = $link->findAll(['weblink_id' => $old_weblink_id]);
        foreach ($links as $link) {
            $link->setId(null)->setWeblinkId($this->getId())->save();
        }

        return $this;
    }

}
