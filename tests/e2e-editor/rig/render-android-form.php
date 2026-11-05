<?php
// Prints the real markup of Application_Form_Android (the editor's Advanced settings), without the app.
// usage: php render-android-form.php /path/to/siberian-src > form.html
$src = $argv[1];
require $src . '/tests/php-upgrade/bootstrap.php';

if (!function_exists('__')) {
    function __($t, ...$a) { return $a ? vsprintf($t, $a) : $t; }
}
if (!function_exists('__path')) {
    function __path($p) { return '/' . ltrim($p, '/'); }
}
if (!function_exists('p__')) {
    function p__($c, $t, ...$a) { return $a ? vsprintf($t, $a) : $t; }
}
require_once $src . '/siberian/app/sae/modules/Application/Form/Android.php';

$view = new Zend_View();
Zend_Controller_Action_HelperBroker::addHelper(new Zend_Controller_Action_Helper_ViewRenderer($view));
$form = new Application_Form_Android();
$app = new class {
    public function __call($n, $a) { return false; }
};
$form->fill($app);
echo $form->render($view);
