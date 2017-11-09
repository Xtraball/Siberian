<?php

class Wordpress_Mobile_ListController extends Application_Controller_Mobile_Default
{

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {
                $option_value = $this->getCurrentOptionValue();
                $wordpress = $option_value->getObject();
                $offset = $this->getRequest()->getParam('offset', 0);

                $posts = $wordpress->getRemotePosts($this->isOverview(), null, !$this->isOverview(), $offset);
                $cover = null;

                if(count($posts) AND $offset == 0) {
                    foreach($posts as $k => $post) {
                        if(!$post->getIsHidden()) {
                            if(!$cover) {
                                $cover = $post;
                            }
                        }
                    }

                    if($cover AND $cover->getPicture()) {
                        $cover->setIsHidden(true);
                    } else {
                        $cover = null;
                    }
                }

                $data = array("collection" => array(), "cover" => array());

                foreach($posts as $post) {

                    $data["collection"][] = array(
                        "id"                        => (integer) $post->getId(),
                        "title"                     => $post->getTitle(),
                        "subtitle"                  => html_entity_decode($post->getShortDescription(), ENT_NOQUOTES, "UTF-8"),
                        "description"               => $post->getDescription(),
                        "picture"                   => $post->getPicture(),
                        "date"                      => $post->getFormattedDate(),
                        "is_hidden"                 => (boolean) $post->getIsHidden(),
                        "url"                       => $this->getPath("wordpress/mobile_view", array("value_id" => $value_id, "post_id" => $post->getId())),
                        "social_sharing_active"     => (boolean) $option_value->getSocialSharingIsActive()
                    );

                }

                if($cover) {
                    $data["cover"] = array(
                        "id"            => (integer) $cover->getId(),
                        "title"         => $cover->getTitle(),
                        "subtitle"      => html_entity_decode($cover->getShortDescription(), ENT_NOQUOTES, "UTF-8"),
                        "description"   => html_entity_decode($cover->getDescription(), ENT_NOQUOTES, "UTF-8"),
                        "picture"       => $cover->getPicture(),
                        "date"          => $cover->getFormattedDate(),
                        "is_hidden"     => false,
                        "url"           => $this->getPath("wordpress/mobile_view", array("value_id" => $value_id, "post_id" => $cover->getId()))
                    );
                }

                $data["displayed_per_page"] = Wordpress_Model_Wordpress::DISPLAYED_PER_PAGE;
                $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendJson($data);

        }

    }

}