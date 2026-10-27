<?php
/**
 * Settings > Communications: the SMTP password has the eye toggle, the e-mail test field and its button are a row with
 * a gap (they touched), and the custom SMTP switch always loads as "0" or "1" (a missing or empty value left the
 * select blank instead of "No").
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae';
$template = $root . '/modules/System/resources/design/desktop/backoffice/template/system/config/email.phtml';
$controller = $root . '/modules/System/controllers/Backoffice/Config/EmailController.php';
$directive = $root . '/design/desktop/backoffice/js/directives/sb-password.js';
$backoffice = $root . '/design/desktop/backoffice';

require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Flag.php';

return [
    'the switch is "1" only for a true value, a missing, empty or odd one is "0"' => function () {
        foreach (['1', 1, true, 'true', 'on', 'yes'] as $on) {
            t_equal('1', \Siberian\Flag::toSwitch($on), var_export($on, true));
        }
        foreach ([null, '', '0', 0, false, 'no', 'false', 'off', [], ['1'], 'abc', '2'] as $off) {
            t_equal('0', \Siberian\Flag::toSwitch($off), var_export($off, true));
        }
    },

    'the email page reads the custom SMTP switch through the helper' => function () use ($controller) {
        $php = file_get_contents($controller);
        t_true(strpos($php, 'protected function _findconfig()') !== false, 'findconfig is overridden');
        t_true(strpos($php, 'parent::_findconfig()') !== false, 'the common loading is kept');
        t_true(strpos($php, '\\Siberian\\Flag::toSwitch($switch[\'value\'] ?? null)') !== false, 'value normalized');
    },

    'the SMTP username declares its autocomplete, next to the password field' => function () use ($template) {
        $html = file_get_contents($template);
        t_true(preg_match('/<input type="text"\s+class="form-control"\s+autocomplete="username"\s+ng-model="configs\.smtp_credentials\.username"/', $html) === 1, 'autocomplete=username');
    },

    'the SMTP password uses the eye toggle' => function () use ($template) {
        $html = file_get_contents($template);
        t_equal(0, substr_count($html, 'type="password"'), 'no plain password input left');
        t_true(strpos($html, '<sb-password ng-model="configs.smtp_credentials.password"') !== false, 'sb-password');
        t_true(strpos($html, 'toggle-title="<?php echo addcslashes(p__(\'backoffice\', \'Show / hide\'), \'"\'); ?>"') !== false, 'translated title');
    },

    'the test field and its button are one row with a gap' => function () use ($template) {
        $html = file_get_contents($template);
        t_true(strpos($html, '<div class="smtp-test">') !== false, 'row');
        t_true(strpos($html, 'width: 50%') === false, 'no inline width');
        t_equal(0, substr_count($html, '<br />'), 'no line breaks used as spacing');
        t_true(preg_match('/\.smtp-test \{[^}]*display:\s*flex[^}]*gap:\s*12px/s', $html) === 1, 'flex with a gap');
        t_true(preg_match('/<button type="button"\s+class="btn btn-primary"\s+ng-click="testemail\(\);"/', $html) === 1, 'a button, not a bare link');
    },

    'the eye directive toggles the field type and the icon' => function () use ($directive, $backoffice) {
        $js = file_get_contents($directive);
        t_true(strpos($js, "App.directive('sbPassword'") !== false, 'registered');
        t_true(strpos($js, "restrict: 'E'") !== false, 'element');
        t_true(strpos($js, 'ng-attr-type="{{ revealed ?') !== false, 'type bound to the state');
        t_true(strpos($js, 'autocomplete="new-password"') !== false, 'no browser autofill');
        t_true(strpos($js, 'ng-model="ngModel"') !== false, 'bound to the model of the element');
        $css = file_get_contents($backoffice . '/css/font-awesome.min.css');
        foreach (['fa-eye', 'fa-eye-slash'] as $icon) {
            t_true(strpos($css, '.' . $icon . ':before') !== false, $icon . ' in the font');
            t_true(strpos($js, $icon) !== false, $icon . ' used');
        }
    },

    'the eye button is styled for every theme' => function () use ($backoffice) {
        t_true(strpos(file_get_contents($backoffice . '/css/style.css'), '.sb-password .sb-password-toggle {') !== false, 'base');
        t_true(strpos(file_get_contents($backoffice . '/css/theme-default/styles.css'), '.sb-password .sb-password-toggle {') !== false, 'default theme');
    },
];
