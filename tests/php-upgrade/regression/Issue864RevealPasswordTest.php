<?php
/**
 * #864: the custom SMTP password of the editor was a plain text input, always visible. It is now a password input
 * (hidden by default) with an eye button to reveal it, a reusable element for credentials. The saved value stays in
 * the field so saving again does not erase it, and the other password inputs are untouched.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
$lib = $siberian . '/lib';
$domainForm = $siberian . '/app/sae/modules/Application/Form/Domain.php';
$abstractForm = $lib . '/Siberian/Form/Abstract.php';
$elementFile = $lib . '/Siberian/Form/Element/RevealPassword.php';
$featuresJs = $siberian . '/app/sae/design/desktop/flat/js/features.js';
$catalog = $siberian . '/languages/default/default.csv';

set_include_path($lib . PATH_SEPARATOR . get_include_path());
spl_autoload_register(static function ($class) use ($lib) {
    $file = $lib . '/' . str_replace('_', '/', $class) . '.php';
    if (strpos($class, 'Zend_') === 0 && is_file($file)) {
        require_once $file;
    }
});
if (!function_exists('__')) {
    function __($text)
    {
        return $text;
    }
}

/** The rendered helper, with the real Zend view. */
$render = function (string $value, array $attribs = []) use ($lib) {
    require_once $lib . '/Siberian/View/Helper/FormRevealPassword.php';
    $view = new Zend_View();
    $view->addHelperPath($lib . '/Siberian/View/Helper/', 'Siberian_View_Helper');
    return $view->formRevealPassword('smtp_password', $value, array_merge(['id' => 'password'], $attribs));
};

return [
    '864 the input is a password input, hidden by default' => function () use ($render) {
        $html = $render('s3cret');
        t_true(strpos($html, 'type="password"') !== false, 'type=password');
        t_true(strpos($html, 'type="text"') === false, 'never text');
        t_true(strpos($html, 'autocomplete="new-password"') !== false, 'no browser autofill of the editor login');
        t_true(strpos($html, 'data-reveal-input') !== false, 'marked for the toggle');
    },

    '864 the saved value stays in the field and is escaped' => function () use ($render) {
        $html = $render('a"b<c');
        t_true(strpos($html, 'value="a&quot;b&lt;c"') !== false, 'value kept, escaped');
        t_true(strpos($html, 'a"b<c') === false, 'raw value never printed');
    },

    '864 the eye button is accessible and does not submit the form' => function () use ($render) {
        $html = $render('x');
        t_true(strpos($html, '<button type="button" data-reveal-toggle aria-pressed="false"') !== false, 'button type=button');
        t_true(strpos($html, 'aria-label="Show / hide the password"') !== false, 'label');
        t_equal(1, substr_count($html, '<input'), 'one input');
    },

    '864 the element keeps the value (renderPassword) and uses the helper' => function () {
        $defaults = (new ReflectionClass('Siberian_Form_Element_RevealPassword'))->getDefaultProperties();
        t_equal('formRevealPassword', $defaults['helper'], 'helper');
        t_equal(true, $defaults['renderPassword'], 'value rendered');
        t_true(is_subclass_of('Siberian_Form_Element_RevealPassword', 'Siberian_Form_Element_Password'), 'extends the password element');
    },

    '864 only the SMTP password changes, the other inputs stay as they were' => function () use ($domainForm, $abstractForm, $siberian) {
        $code = file_get_contents($domainForm);
        t_true(strpos($code, '$smtp_password = $this->addSimpleRevealPassword("password", __("Password"));') !== false, 'smtp password');
        t_true(strpos($code, '$smtp_username = $this->addSimpleText("username", __("Username"));') !== false, 'username untouched');
        t_true(strpos($code, '$smtp_server = $this->addSimpleText("server", __("Server"));') !== false, 'server untouched');
        $abstract = file_get_contents($abstractForm);
        t_true(strpos($abstract, 'public function addSimplePassword($name, $label = "", $placeholder = false)') !== false, 'addSimplePassword kept');
        t_true(strpos($abstract, 'public function addSimpleRevealPassword(') !== false, 'new element helper');
        $wordpress = file_get_contents($siberian . '/app/sae/modules/Wordpress2/Form/Wordpress.php');
        t_true(strpos($wordpress, "addSimplePassword('password'") !== false, 'other users of passwords untouched');
    },

    '864 the toggle is delegated and the field is hidden again on submit' => function () use ($featuresJs) {
        $js = file_get_contents($featuresJs);
        t_true(strpos($js, ".on('click.sbRevealPassword', '[data-reveal-toggle]'") !== false, 'delegated click');
        t_true(strpos($js, ".on('submit.sbRevealPassword', 'form'") !== false, 'hidden again on submit');
        t_true(strpos($js, "box.find('input[data-reveal-input]').attr('type', show ? 'text' : 'password');") !== false, 'toggles the type');
    },

    '864 the handlers cannot be bound twice and do not rely on a synthetic click' => function () use ($featuresJs) {
        $js = file_get_contents($featuresJs);
        t_equal(1, substr_count($js, ".off('click.sbRevealPassword').on('click.sbRevealPassword'"), 'click unbound before it is bound');
        t_equal(1, substr_count($js, ".off('submit.sbRevealPassword').on('submit.sbRevealPassword'"), 'submit unbound before it is bound');
        t_true(strpos($js, "\$(document).on('click', '[data-reveal-toggle]'") === false, 'no plain handler left');
        $block = substr($js, strpos($js, 'window.sbSetPasswordRevealed = function'));
        t_true(strpos($block, "trigger('click')") === false, 'submit resets the field directly');
        t_equal(1, substr_count($js, 'window.sbSetPasswordRevealed = function'), 'one definition');
    },

    '864 the button text is in the translation catalog (English is the default)' => function () use ($catalog) {
        t_true(strpos(file_get_contents($catalog), "\n\"Show / hide the password\"\n") !== false, 'catalog entry');
    },
];
