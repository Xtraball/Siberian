<?php
/**
 * Characterization tests for the bundled Zend Framework 1. The values asserted
 * here are what PHP 8.2 produces today; an upgrade must not change them.
 */
return [
    'Zend_Json round trip' => function () {
        $data = ['a' => 1, 'b' => [true, null, 'x'], 'c' => ['d' => 1.5]];
        t_equal($data, Zend_Json::decode(Zend_Json::encode($data)), 'round trip');
        t_equal('{"k":"v"}', Zend_Json::encode(['k' => 'v']), 'encode');
    },

    'Zend_Filter string filters' => function () {
        t_equal('abc123', (new Zend_Filter_Alnum())->filter('a b-c_1!2@3'), 'alnum');
        t_equal('abc', (new Zend_Filter_Alpha())->filter('a1b2c3'), 'alpha');
        t_equal('123', (new Zend_Filter_Digits())->filter('a1b2c3'), 'digits');
        t_equal('x', (new Zend_Filter_StringTrim())->filter('  x  '), 'trim');
        t_equal('abc', (new Zend_Filter_StringToLower())->filter('ABC'), 'lower');
        t_equal('ABC', (new Zend_Filter_StringToUpper())->filter('abc'), 'upper');
        t_equal('42', (string) (new Zend_Filter_Int())->filter('42.9'), 'int');
    },

    'Zend_Filter_StripTags drops script tags' => function () {
        $out = (new Zend_Filter_StripTags())->filter('<b>hi</b><script>alert(1)</script>');
        t_true(strpos($out, '<script') === false, 'script stripped');
        t_true(strpos($out, 'hi') !== false, 'text kept');
    },

    'Zend_Validate_EmailAddress' => function () {
        $v = new Zend_Validate_EmailAddress();
        t_true($v->isValid('user@example.com'), 'valid');
        t_true(!$v->isValid('not-an-email'), 'invalid');
    },

    'Zend_Validate numeric rules' => function () {
        t_true((new Zend_Validate_Int())->isValid('12'), 'int');
        t_true(!(new Zend_Validate_Int())->isValid('1.5'), 'not int');
        t_true((new Zend_Validate_Between(['min' => 1, 'max' => 5]))->isValid(3), 'between');
        t_true(!(new Zend_Validate_Between(['min' => 1, 'max' => 5]))->isValid(9), 'out of range');
    },

    'Zend_Uri parsing' => function () {
        $uri = Zend_Uri::factory('https://example.com:8080/a/b?x=1');
        t_equal('https', $uri->getScheme(), 'scheme');
        t_equal('example.com', $uri->getHost(), 'host');
        t_equal('8080', (string) $uri->getPort(), 'port');
        t_equal('/a/b', $uri->getPath(), 'path');
        t_equal('x=1', $uri->getQuery(), 'query');
    },

    'Zend_Registry' => function () {
        Zend_Registry::set('upgrade-test', 'value');
        t_equal('value', Zend_Registry::get('upgrade-test'), 'get');
        t_true(Zend_Registry::isRegistered('upgrade-test'), 'registered');
    },

    // The 8.1 return-type deprecations live here; this must keep working
    // whether the signature is fixed or merely suppressed.
    'Zend_Reflection_Class overrides still work' => function () {
        $class = new Zend_Reflection_Class('Zend_Json');
        t_true($class->getStartLine() > 0, 'start line');
        t_equal('encode', $class->getMethod('encode')->getName(), 'method');
        t_true(count($class->getMethods()) > 0, 'methods');
    },

    'Zend_Date formats a fixed timestamp' => function () {
        $date = new Zend_Date(86400 * 365, Zend_Date::TIMESTAMP);
        $date->setTimezone('UTC');
        t_equal('1971-01-01', $date->toString('yyyy-MM-dd'), 'date');
    },
];
