<?php
/**
 * Class Application_Form_PrivacyPolicy
 */
class Application_Form_PrivacyPolicy extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/application/settings_tc/saveprivacypolicy"))
            ->setAttrib("id", "form-application-privacy-policy")
        ;

        /** Bind as a onchange form */
        self::addClass("create", $this);

        $privacy_policy = $this->addSimpleTextarea("privacy_policy", "", false, array("ckeditor" => "complete"));
        $privacy_policy
            ->setNewDesignLarge()
            ->setRichtext()
        ;

        $reset = $this->addSubmit(__("Reset to default Privacy Policy."), "reset_default");
        $reset->addClass("pull-right");

        $this->groupElements("reset", array("reset_default"));

        if (isGdpr()) {
            $this->addSimpleHtml('separator', '<br /><br /><h4 style="font-weight: bold;">' . __('Privacy policy section for GDPR') . '</h4>', [
                'class' => 'col-md-12'
            ]);

            $privacyPolicyGdpr = $this->addSimpleTextarea('privacy_policy_gdpr', '', false, array('ckeditor' => 'complete'));
            $privacyPolicyGdpr
                ->setNewDesignLarge()
                ->setRichtext();

            $reset = $this->addSubmit(__("Reset to default Privacy Policy GDPR."), "reset_default_gdpr");
            $reset->addClass("pull-right");

            $this->groupElements("reset_gdpr", ['reset_default_gdpr']);
        }

        $this->addNav("save", "Save", false, true);
    }
}