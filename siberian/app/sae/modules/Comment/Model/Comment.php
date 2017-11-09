<?php
class Comment_Model_Comment extends Core_Model_Default {

    protected $_is_cacheable = true;
    const DISPLAYED_PER_PAGE = 10;

    protected $_answers;
    protected $_likes;
    protected $_customer;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Comment_Model_Db_Table_Comment';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "newswall-list",
                "offline" => true,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    public function getFeaturePaths($option_value) {
        if(!$this->isCacheable()) {
            return array();
        }

        $paths = array();

        $value_id = $option_value->getId();
        $cache_id = "feature_paths_valueid_{$value_id}";
        if(!$result = $this->cache->load($cache_id)) {

            if($option_value->getCode() === "newswall") {
                $paths[] = $option_value->getPath("comment/mobile_gallery/findall", array('value_id' => $option_value->getId()), false);
                $paths[] = $option_value->getPath("comment/mobile_map/findall", array('value_id' => $option_value->getId()), false);

                // Newswall path
                $params = array(
                    'value_id' => $option_value->getId(),
                    'offset' => 0
                );

                $comment = new Comment_Model_Comment();
                $comments = $comment->findAll(array("value_id" => $option_value->getId(), "is_visible = ?" => 1), "created_at DESC");
                for($i=0; $i < ceil($comments->count()/Comment_Model_Comment::DISPLAYED_PER_PAGE); $i++) {
                    $params['offset'] = $i*Comment_Model_Comment::DISPLAYED_PER_PAGE;
                    $paths[] = $option_value->getPath("comment/mobile_list/findall", $params, false);
                }

                foreach ($comments as $comment) {
                    $params = array(
                        "comment_id" => $comment->getId(),
                        "value_id" => $option_value->getId()
                    );
                    $paths[] = $this->getPath("comment/mobile_view/find", $params, false);
                    $paths[] = $this->getPath("comment/mobile_comment/findall", array("comment_id" => $comment->getId()), false);
                }
            }

            $this->cache->save($paths, $cache_id, array(
                "feature_paths",
                "feature_paths_valueid_{$value_id}"
            ));
        } else {
            $paths = $result;
        }

        return $paths;
    }

    public function getAssetsPaths($option_value) {
        if(!$this->isCacheable()) {
            return array();
        }

        $value_id = $option_value->getId();
        $cache_id = "assets_paths_valueid_{$value_id}";
        if(!$result = $this->cache->load($cache_id)) {

            $paths = array();

            if($option_value->getCode() === "newswall") {

                $application = $this->getApplication();
                $paths[] = $application->getIcon(74);

                $comment = new Comment_Model_Comment();
                $comments = $comment->findAll(array("value_id" => $option_value->getId(), "is_visible = ?" => 1), "created_at DESC");

                foreach($comments as $comment) {
                    if($comment->getImageUrl())
                        $paths[] = $comment->getImageUrl();

                    $matches = array();
                    $regex_url = "/((?:http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(?:\/[^\s\"]*)\.(?:png|gif|jpeg|jpg))+/";
                    preg_match_all($regex_url, $comment->getText(), $matches);

                    $matches = call_user_func_array('array_merge', $matches);

                    if($matches && count($matches) > 1) {
                        unset($matches[0]);
                        $paths = array_merge($paths, $matches);
                    }

                    $answer = new Comment_Model_Answer();
                    $answers = $answer->findByComment($comment->getId());

                    foreach($answers as $answer) {
                        $paths[] = __path("/customer/mobile_account/avatar/", array("customer" => $answer->getCustomerId()));
                    }
                }
            }

            $this->cache->save($paths, $cache_id, array(
                "assets_paths",
                "assets_paths_valueid_{$value_id}"
            ));
        } else {
            $paths = $result;
        }

        return $paths;
    }

    public function findLast($value_id, $pos_id) {
        $row = $this->getTable()->findLast($value_id, $pos_id);
        if($row) {
            $this->setData($row->getData())
                ->setId($row->getId())
            ;
        }
        return $this;
    }

    public function findLastest($value_id) {
        return $comments = $this->getTable()->findLastest($value_id);
    }

    public function findAllWithPhoto($value_id) {
        return $comments = $this->getTable()->findAllWithPhoto($value_id);
    }

    public function findAllWithLocation($value_id, $offset) {
        return $comments = $this->getTable()->findAllWithLocation($value_id, $offset);
    }

    public function findAllWithLocationAndPhoto($value_id) {
        return $comments = $this->getTable()->findAllWithLocationAndPhoto($value_id);
    }

    public function pullMore($value_id, $start, $count) {
        return $comments = $this->getTable()->pullMore($value_id, $start, $count);
    }

    public function getImageUrl() {
        $image_path = Application_Model_Application::getImagePath().$this->getData('image');
        $base_image_path = Application_Model_Application::getBaseImagePath().$this->getData('image');
        if($this->getData('image') AND file_exists($base_image_path)) {
            return $image_path;
        }
        return null;
    }

    public function getAnswers() {
        if(!$this->getId()) return array();
        if(is_null($this->_answers)) {
            $answer = new Comment_Model_Answer();
            $answer->setStatus($this);
            $this->_answers = $answer->findByComment($this->getId(), true);
            foreach($this->_answers as $answer) {
                $answer->setComment($this);
            }
        }

        return $this->_answers;
    }

    public function getLikes() {
        if(!$this->getId()) return array();
        if(is_null($this->_likes)) {
            $like = new Comment_Model_Like();
            $this->_likes = $like->findByComment($this->getId());
            foreach($this->_likes as $like) {
                $like->setComment($this);
            }
        }

        return $this->_likes;
    }

    public function getCustomer() {
        if(is_null($this->_customer)) {
            $customer = new Customer_Model_Customer();
            $this->_customer = $customer->find($this->getCustomerId());
        }

        return $this->_customer;
    }

    public function createDummyContents($option_value, $design, $category) {

        $option = new Application_Model_Option();
        $option->find($option_value->getOptionId());

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        if($dummy_content_xml->{$option->getCode()}) {
            foreach ($dummy_content_xml->{$option->getCode()}->children() as $content) {
                $this->unsData();

                $this->addData((array)$content)
                    ->setValueId($option_value->getId())
                    ->save();
            }
        }

    }

    public function copyTo($option) {
        $this->setId(null)
            ->setValueId($option->getId())
        ;

        if($image_url = $this->getImageUrl()) {

            $file = pathinfo($image_url);
            $filename = $file['basename'];

            $relativePath = $option->getImagePathTo();
            $folder = Core_Model_Directory::getBasePathTo(Application_Model_Application::PATH_IMAGE.$relativePath);

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

        return $this;
    }

}
