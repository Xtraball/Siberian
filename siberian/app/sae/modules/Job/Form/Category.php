<?php
/**
 * Class Job_Form_Category
 */
class Job_Form_Category extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/job/category/editpost"))
            ->setAttrib("id", "form-category")
            ->addNav("job-category-nav")
        ;

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("category_id");

        $name = $this->addSimpleText("name", __("Name"));
        $name
            ->setRequired(true)
        ;

        $description = $this->addSimpleTextarea("description", __("Description"));
        $description
            ->setRequired(true)
            ->setNewDesignLarge()
            ->setRichtext()
        ;

        $icon = $this->addSimpleImage("icon", __("Icon"), __("Import an icon"), array("width" => 500, "height" => 500, "required" => true));

        $keywords = $this->addSimpleText("keywords", __("Keywords"));

        $job_id = $this->addSimpleHidden("job_id");
        $job_id
            ->setRequired(true)
        ;

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }

    public function setCategoryId($category_id) {
        $this->getElement("category_id")->setValue($category_id)->setRequired(true);
    }
}