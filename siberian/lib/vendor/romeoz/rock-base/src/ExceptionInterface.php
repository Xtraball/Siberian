<?php

namespace rock\base;


interface ExceptionInterface
{
    const PHP_VERSION_INVALID = 'PHP invalid version.';
    const UNKNOWN_CLASS = 'Unknown class: {{class}}.';
    const UNKNOWN_METHOD = 'Unknown method: {{method}}.';
    const SETTING_UNKNOWN_PROPERTY = 'Setting unknown property: {{class}}::{{property}}.';
    const SETTING_READ_ONLY_PROPERTY = 'Setting read-only property: {{class}}::{{property}}.';
    const GETTING_UNKNOWN_PROPERTY = 'Getting unknown property: {{class}}::{{property}}.';
    const GETTING_WRITE_ONLY_PROPERTY = 'Getting write-only property: {{class}}::{{property}}.';
    const UNKNOWN_VAR = 'Empty/Unknown var: {{name}}.';
    const UNKNOWN_PROPERTY = 'Empty/Unknown property: {{name}}.';
    const UNKNOWN_FILE = 'Unknown file: {{path}}.';
    const NOT_CREATE_FILE = 'Does not create file: {{path}}.';
    const NOT_CREATE_DIR = 'Does not create dir: {{path}}.';
    const NOT_CALLABLE = 'Does not callable: {{name}}.';
    const NOT_OBJECT = 'Does not object: {{name}}.';
    const NOT_ARRAY = 'Does not array: {{name}}.';
    const WRONG_TYPE = 'Wrong type: {{name}}.';
    const NOT_INSTALL_LIBRARY = 'Library "{{name}}" does not install.';
    const NOT_INSTALL_CSRF = 'Library "Rock CSRF" does not install.';
    const NOT_INSTALL_TEMPLATE = 'Library "Rock Template" does not install.';
    const NOT_INSTALL_FILE = 'Library "Rock File" does not install.';
    const NOT_INSTALL_DI = 'Library "Rock DI" does not install.';
    const NOT_INSTALL_REQUEST = 'Library "Rock Request" does not install.';
    const NOT_INSTALL_RESPONSE = 'Library "Rock Response" does not install.';
    const NOT_INSTALL_I18N = 'Library "Rock i18n" does not install.';
    const NOT_INSTALL_IMAGE = 'Library "Rock Image" does not install.';
    const NOT_INSTALL_FILTERS = 'Library "Rock Filters" does not install.';
    const NOT_INSTALL_CAPTCHA = 'Library "Rock Captcha" does not install.';
    const NOT_INSTALL_URL = 'Library "Rock URL" does not install.';
    const NOT_INSTALL_VALIDATE = 'Library "Rock Validate" does not install.';
    const NOT_INSTALL_SANITIZE = 'Library "Rock Sanitize" does not install.';
    const NOT_INSTALL_DB = 'Library "Rock DB" does not install.';
    const NOT_INSTALL_CACHE = 'Library "Rock Cache" does not install.';

    /**
     * @param string $msg message
     * @param array $placeholders placeholders for replacement
     * @param int $level
     * @param \Exception|null $handler handler
     */
    public function __construct($msg, array $placeholders = [], $level = 0, \Exception $handler = null);
}