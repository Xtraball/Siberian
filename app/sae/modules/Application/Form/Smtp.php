<?php

class Application_Form_Smtp extends Siberian_Form_Abstract  {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/application/settings_advanced/saveform"))
            ->setAttrib("id", "form-application-smtp")
        ;

        /** Bind as a create form */
        self::addClass("create", $this);

        $sender_name = $this->addSimpleText("sender_name", __("Name"));
        $sender_email = $this->addSimpleText("sender_email", __("E-mail"));

        $sender_name->setBelongsTo('smtp_credentials');
        $sender_email->setBelongsTo('smtp_credentials');

        $this->groupElements("sender", array(
            "sender_name",
            "sender_email"
        ), __("Sender name/e-mail"));

        $enable_custom_smtp = $this->addSimpleCheckbox("enable_custom_smtp", __("Enable custom SMTP"));
        $enable_custom_smtp
            ->setRequired(true)
        ;

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
            "auth",
            "server",
            "username",
            "password",
            "ssl",
            "port",
        ), __("SMTP Configuration"));

        $this->addNav("submit", "Save", false);

    }
}