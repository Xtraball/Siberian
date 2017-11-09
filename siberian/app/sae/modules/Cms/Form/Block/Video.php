<?php

class Cms_Form_Block_Video extends Cms_Form_Block_Abstract {

    /**
     * @var string
     */
    public $blockType = 'video';

    public function init() {
        parent::init();

        $this
            ->setAttrib("id", "form-cms-block-video-".$this->uniqid)
        ;

        # YOUTUBE
        $youtube_search = $this->addSimpleText("youtube_search", __("Make a search or enter the Youtube Url:"));
        $youtube_search->setBelongsTo("block[".$this->uniqid."][video]");
        $youtube_search->addClass("cms-video-input cms-video-youtube");

        $youtube = $this->addSimpleHidden("youtube");
        $youtube->setBelongsTo("block[".$this->uniqid."][video]");
        $youtube->addClass("cms-video-youtube-id");

        # PODCAST
        $podcast_search = $this->addSimpleText("podcast_search", __("Podcast URL"));
        $podcast_search->setBelongsTo("block[".$this->uniqid."][video]");
        $podcast_search->addClass("cms-video-input cms-video-podcast");

        $podcast = $this->addSimpleHidden("podcast");
        $podcast->setBelongsTo("block[".$this->uniqid."][video]");
        $podcast->addClass("cms-video-podcast-id");


        # VIDEO
        $video_cover = $this->addSimpleImage("cover", __("Loading picture"), __("Loading picture"), array(
            "width" => 1000,
            "height" => 600,
        ));
        $video_cover->setBelongsTo("block[".$this->uniqid."][video]");
        $video_cover->addClass("cms-video-input cms-video-link");

        $video_cover_hidden = $this->addSimpleHidden("cover_image");
        $video_cover_hidden->setBelongsTo("block[".$this->uniqid."][video]");
        $video_cover_hidden->addClass("cms-video-cover-image");

        $video_url = $this->addSimpleText("video", __("Video URL"));
        $video_url->setBelongsTo("block[".$this->uniqid."][video]");
        $video_url->addClass("cms-video-input cms-video-link");
        $video_url->setDescription(__(".mp4 or .3gp format"));

        $video_description = $this->addSimpleText("description", __("Description"));
        $video_description->setBelongsTo("block[".$this->uniqid."][video]");
        $video_description->addClass("cms-video-input cms-video-link");


        # BUTTON TYPE
        $type = $this->addSimpleHidden("type");
        $type->setBelongsTo("block[".$this->uniqid."][video]");
        $type->addClass("cms-video-input cms-video-type");


        # VALUE ID
        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }

    /**
     * @param $block
     * @return $this
     */
    public function loadBlock($block) {

        $video_object = $block->getObject();

        switch($block->getTypeId()) {
            case "youtube":
                    $this->getElement("youtube_search")->setValue($video_object->getTypeInstance()->getSearch());
                    $this->getElement("youtube")->setValue($video_object->getTypeInstance()->getYoutube());
                    $this->getElement("type")->setValue("youtube");
                break;
            case "podcast":
                    $this->getElement("podcast_search")->setValue($video_object->getTypeInstance()->getSearch());
                    $this->getElement("podcast")->setValue($video_object->getTypeInstance()->getLink());
                    $this->getElement("type")->setValue("podcast");
                break;
            case "link":
                    $this->getElement("description")->setValue($block->getDescription());
                    $this->getElement("cover")->setValue($block->getImage());
                    $this->getElement("cover_image")->setValue($block->getImage());
                    $this->getElement("video")->setValue($video_object->getTypeInstance()->getLink());
                    $this->getElement("type")->setValue("link");
                break;
        }

        return $this;
    }

}