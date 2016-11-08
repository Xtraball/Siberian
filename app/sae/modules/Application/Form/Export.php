<?php
/**
 * Class Job_Form_Company
 */
class Application_Form_Export extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/application/customization_features/export"))
            ->setAttrib("id", "form-application-export")
        ;

        /** Bind as a onchange form */
        self::addClass("create", $this);
    }

    public function addOptions($application) {
        $opts = array();
        $elements = array();

        foreach($application->getOptions() as $opt) {
            $option_id = $opt->getId();
            $feature = $opt->getCode();
            if(Siberian_Exporter::isRegistered($feature)) {
                $label = __($opt->getName())." (".$opt->getTabbarName().")";
                if(Siberian_Exporter::hasOptions($feature)) {
                    $options = Siberian_Exporter::getOptions($feature);

                    array_unshift($options, __("Do not export"));

                    $el = $this->addSimpleSelect($option_id, $label, $options);
                    $el->setOptions(array(
                        'belongsTo' => 'options',
                        'value' => $option_id
                    ));
                    $el->setValue("safe");
                } else {
                    $el = $this->addSimpleCheckbox($option_id, $label);
                    $el->setOptions(array(
                        'belongsTo' => 'options',
                        'value' => $option_id
                    ));
                    $el->setValue(true);
                }

                $el->setNewDesign();

                $elements[] = $el;
            }
        }

        if(!empty($elements)) {
            $this->addDisplayGroup($elements, __("Features to export & Options"));
        }

    }

    public function addTemplate() {
        $is_template = $this->addSimpleCheckbox("is_template", __("Export as global Template"));
        $is_template->setValue(false);

        $template_preview = $this->addSimpleImage(
            "template_preview",
            __("Template preview"),
            __("Template preview"),
            array("width" => 640, "height" => 1136)
        );
        $template_preview->addClass("toggle_template");

        $template_name = $this->addSimpleText("template_name", __("Template name"));
        $template_name->addClass("toggle_template");

        $template_version = $this->addSimpleText("template_version", __("Template version"));
        $template_version->addClass("toggle_template");

        $template_description = $this->addSimpleText("template_description", __("Template description"));
        $template_description->addClass("toggle_template");

        $this->addNav("export_selection", "Export App & Selected features.", false, true);
    }

    public function isTemplate() {
        $this->getElement("template_preview")->setRequired(true);
        $this->getElement("template_name")->setRequired(true);
        $this->getElement("template_version")->setRequired(true);
    }
}