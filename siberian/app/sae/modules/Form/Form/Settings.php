<?php

/**
 * Class Form_Form_Settings
 */
class Form_Form_Settings extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/form/application/edit-settings"))
            ->setAttrib("id", "form-form-settings");

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleText("email", p__("form", "Recipient emails"));

        $text = p__("form", "You can add multiple e-mails, separated by a coma.");
        $rawHint = <<<HTML
<div class="alert alert-info">
    {$text}
</div>
HTML;

        $this->addSimpleHtml("raw_hint", $rawHint, ["class" => "col-md-offset-3 col-md-7"]);

        $this->addSimpleSelect("design", p__("form","Design"), [
            "list" => p__("form", "List"),
            "card" => p__("form", "Card"),
        ]);

        $this->addSimpleSelect("date_format", p__("form","Date format"), [
            "MM/DD/YYYY HH:mm" => "MM/DD/YYYY HH:mm",
            "DD/MM/YYYY HH:mm" => "DD/MM/YYYY HH:mm",
            "MM DD YYYY HH:mm" => "MM DD YYYY HH:mm",
            "DD MM YYYY HH:mm" => "DD MM YYYY HH:mm",
            "YYYY-MM-DD HH:mm" => "YYYY-MM-DD HH:mm",
            "YYYY MM DD HH:mm" => "YYYY MM DD HH:mm",
        ]);
        
        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true);

        $submit = $this->addSubmit(p__("form", "Save"));
        $submit->addClass("pull-right");
    }
}