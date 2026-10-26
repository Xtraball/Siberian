<?php
/**
 * Source Code feature: saving from the editor must not read, nor reset, allow_offline when the form does not post it.
 */
$module = dirname(__DIR__, 3) . '/siberian/app/sae/modules/Sourcecode';

if (!class_exists('Sourcecode_Model_Sourcecode_Post', false)) {
    require_once $module . '/Model/Sourcecode/Post.php';
}

return [
    'sourcecode a save without allow_offline leaves it out, so the stored value is kept' => function () {
        $post = ['html_code' => '<p>Hello</p>', 'value_id' => '12'];
        t_equal($post, Sourcecode_Model_Sourcecode_Post::normalize($post), 'untouched');
    },

    'sourcecode a posted allow_offline is converted to a boolean' => function () {
        $normalize = ['Sourcecode_Model_Sourcecode_Post', 'normalize'];
        t_equal(true, $normalize(['allow_offline' => '1'])['allow_offline'], 'on');
        t_equal(false, $normalize(['allow_offline' => '0'])['allow_offline'], 'off');
        t_equal(false, $normalize(['allow_offline' => ''])['allow_offline'], 'empty');
        t_equal('<b>x</b>', $normalize(['allow_offline' => '1', 'html_code' => '<b>x</b>'])['html_code'], 'other fields');
    },

    'sourcecode the controller goes through the helper' => function () use ($module) {
        $code = file_get_contents($module . '/controllers/ApplicationController.php');
        t_true(strpos($code, '$datas = Sourcecode_Model_Sourcecode_Post::normalize($datas);') !== false, 'helper used');
        t_true(strpos($code, '$datas["allow_offline"]') === false, 'no direct read of the key');
    },
];
