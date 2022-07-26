<?php

/**
 * Class Application_Form_Domain
 */
class Application_Form_Domain extends Siberian_Form_Abstract
{
    /**
     * @var string
     */
    public $color = 'color-blue';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("application/settings_domain/save"))
            ->setAttrib("id", "form-settings-domain");

        self::addClass("create", $this);

        //
        $textWarning = p__("application", "Be careful with custom smtp, an incorrect configuration can break your outbound emails.");
        $warning = <<<RAW
<div class="col-md-12">
    <div class="alert alert-warning">
        {$textWarning}
    </div>
</div>
RAW;

        $this->addSimpleHtml("helper_warning_danger", $warning);

        // Domain
        $this->addSimpleText(
            "domain",
            p__("application", "Domain name"));

        $enable_custom_smtp = $this->addSimpleCheckbox("enable_custom_smtp", __("Enable custom SMTP"));
        $enable_custom_smtp
            ->setRequired(true)
        ;

        $this->groupElements("sender", array(
            "helper_warning_danger",
            "enable_custom_smtp"
        ), __("Custom SMTP"));

        $sender_name = $this->addSimpleText("sender_name", __("Sender name"));
        $sender_email = $this->addSimpleText("sender_email", __("Sender e-mail"));

        $sender_name->setBelongsTo('smtp_credentials');
        $sender_email->setBelongsTo('smtp_credentials');


        $smtp_auth = $this->addSimpleSelect("auth", __("Auth"), array(
            "login" => __("Login"),
            "plain" => __("Plain"),
            "crammd5" => __("Cram-MD5"),
        ));

        $smtp_server = $this->addSimpleText("server", __("Server"));
        $smtp_username = $this->addSimpleText("username", __("Username"));
        $smtp_password = $this->addSimpleText("password", __("Password"));
        $smtp_ssl = $this->addSimpleSelect("ssl", __("SSL"), array(
            "" => __("Default (none)"),
            "tls" => __("TLS"),
            "ssl" => __("SSL"),
        ));
        $smtp_port = $this->addSimpleText("port", __("Port"));

        # Belongs to array
        $smtp_auth->setBelongsTo('smtp_credentials');
        $smtp_server->setBelongsTo('smtp_credentials');
        $smtp_username->setBelongsTo('smtp_credentials');
        $smtp_password->setBelongsTo('smtp_credentials');
        $smtp_ssl->setBelongsTo('smtp_credentials');
        $smtp_port->setBelongsTo('smtp_credentials');

        $this->groupElements("smtp", array(
            "sender_name",
            "sender_email",
            "auth",
            "server",
            "username",
            "password",
            "ssl",
            "port",
        ), __("SMTP Configuration"));

        $submit = $this->addSubmit(__("Save"));
        $submit->addClass("pull-right");
    }
}