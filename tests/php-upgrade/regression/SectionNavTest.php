<?php
/**
 * Backoffice: Settings > Email shows its three unrelated sections (Support, Custom SMTP, E-Mail render preview) as
 * panes of a side navigation instead of one long page: the first one is shown, the left menu switches. The pattern is
 * the reusable sb-section-nav / sb-section-pane pair, loaded from the directives folder, structured in the shared
 * style.css (every theme) and styled by the default theme.
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae';
$backoffice = $root . '/design/desktop/backoffice';
$directive = $backoffice . '/js/directives/sb-section-nav.js';
$email = $root . '/modules/System/resources/design/desktop/backoffice/template/system/config/email.phtml';
$base = $backoffice . '/css/style.css';
$theme = $backoffice . '/css/theme-default/styles.css';
$layout = $backoffice . '/layout/front.xml';
$settingsJs = $root . '/modules/System/resources/design/desktop/backoffice/js/controllers/settings.js';
$general = $root . '/modules/System/resources/design/desktop/backoffice/template/system/config/general.phtml';
$icons = $backoffice . '/css/font-awesome.min.css';

return [
    'the directives are in the folder the backoffice loads' => function () use ($directive, $layout) {
        t_true(is_file($directive));
        t_true(strpos(file_get_contents($layout), '<directives folder="js/directives" />') !== false);
    },

    'the pair is registered and the nav selects the first pane by default' => function () use ($directive) {
        $js = file_get_contents($directive);
        t_true(strpos($js, "directive('sbSectionNav'") !== false, 'sbSectionNav');
        t_true(strpos($js, "directive('sbSectionPane'") !== false, 'sbSectionPane');
        t_true(strpos($js, "require: '^sbSectionNav'") !== false, 'pane needs its nav');
        t_true(strpos($js, 'panes.length === 1') !== false, 'first pane is the default');
        t_true(strpos($js, 'ng-show="active"') !== false, 'panes are hidden, not removed (their fields stay in the form)');
        t_true(strpos($js, 'ng-if="active"') === false);
    },

    'the pane keeps the card markup of sb-section' => function () use ($backoffice, $directive) {
        $section = file_get_contents($backoffice . '/js/directives/sb-section.js');
        $js = file_get_contents($directive);
        foreach (['<div class="area">', '<div class="title {{ klass }}">', '<h2 ng-bind-html="title"></h2>', '<div class="content" ng-transclude></div>'] as $markup) {
            t_true(strpos($section, $markup) !== false, "sb-section has $markup");
            t_true(strpos($js, $markup) !== false, "pane has $markup");
        }
    },

    'the email page has three panes in a nav and no plain section left' => function () use ($email) {
        $html = file_get_contents($email);
        t_equal(3, substr_count($html, '<sb-section-pane '), 'three panes');
        t_equal(3, substr_count($html, '</sb-section-pane>'), 'three closings');
        t_equal(0, substr_count($html, '<sb-section '), 'no plain section');
        t_equal(1, substr_count($html, '<sb-section-nav>'));
        t_equal(1, substr_count($html, '</sb-section-nav>'));
        t_equal(0, substr_count($html, 'ng-submit="save()"'), 'no page-wide save: it would post every section');
    },

    'each section saves only its own settings, the preview has nothing to save' => function () use ($email) {
        $panes = array_slice(explode('<sb-section-pane ', file_get_contents($email)), 1);
        t_equal(3, count($panes));
        $saved = [];
        foreach ([0, 1] as $i) {
            t_equal(1, substr_count($panes[$i], '<form '), "pane $i has its own form");
            t_equal(1, substr_count($panes[$i], 'type="submit"'), "pane $i has a Save button");
            t_true(preg_match('/ng-submit="save\(\[([^\]]+)\]\)"/', $panes[$i], $m) === 1, "pane $i scopes its save");
            preg_match_all("/'([a-z_]+)'/", $m[1], $codes);
            preg_match_all('/ng-model="configs\.([a-z_]+)\./', $panes[$i], $models);
            $shown = array_unique($models[1]);
            $given = $codes[1];
            sort($shown);
            sort($given);
            t_equal(implode(',', $shown), implode(',', $given), "pane $i saves exactly the settings it shows");
            $saved = array_merge($saved, $given);
        }
        t_equal(count($saved), count(array_unique($saved)), 'no setting is saved by two sections');
        t_equal(0, preg_match('/<(input|select|textarea|button|form)\b/', $panes[2]), 'the preview has nothing to save');
    },

    'the settings controller can post a subset and keeps the page-wide save' => function () use ($settingsJs, $general) {
        $js = file_get_contents($settingsJs);
        t_true(strpos($js, '$scope.save = function (codes)') !== false, 'save takes the codes of a section');
        t_true(strpos($js, 'angular.isArray(codes)') !== false, 'no codes means everything');
        t_true(preg_match('/\.save\(values\)/', $js) === 1, 'the filtered values are posted');
        t_true(preg_match('/if \(angular\.isArray\(codes\)\) \{\s*return;\s*\}\s*\/\/ Lazy reload!/', $js) === 1, 'a section save does not reload the page');
        t_true(strpos(file_get_contents($general), 'save()') !== false, 'the general page still saves everything');
    },

    'the panes keep their order: Support first' => function () use ($email) {
        $html = file_get_contents($email);
        $support = strpos($html, '"Support"');
        $smtp = strpos($html, '"Custom SMTP Configuration"');
        $preview = strpos($html, 'E-Mail render preview');
        t_true($support < $smtp && $smtp < $preview);
    },

    'structure is in the shared stylesheet, the look in the default theme' => function () use ($base, $theme) {
        $shared = file_get_contents($base);
        t_true(preg_match('/\.sb-section-nav\s*\{[^}]*display:\s*flex/s', $shared) === 1, 'flex layout for every theme');
        t_true(strpos($shared, '@media (max-width: 767px)') !== false && strpos($shared, '.sb-section-nav-list') !== false, 'small screens');
        $default = file_get_contents($theme);
        t_true(preg_match('/\.sb-section-nav-list\s*>\s*li\.active\s*>\s*a\s*\{[^}]*var\(--sb-accent/s', $default) === 1, 'active item highlighted');
        t_true(preg_match('/\.sb-section-nav-list\s*>\s*li\s*>\s*a\s*\{[^}]*font-size:\s*12px/s', $default) === 1, 'compact menu text (12px)');
    },

    'menu items stay on one line and the page gets more width' => function () use ($base) {
        $shared = file_get_contents($base);
        t_true(preg_match('/\.sb-section-nav-list\s*>\s*li\s*>\s*a\s*\{[^}]*white-space:\s*nowrap[^}]*text-overflow:\s*ellipsis/s', $shared) === 1, 'one line per item');
        t_true(preg_match('/\.sb-section-nav-list\s*\{[^}]*flex:\s*0 0 auto/s', $shared) === 1, 'the menu is as wide as its labels');
        t_true(preg_match('/\.wrapper:has\(\.sb-section-nav\)\s*\{[^}]*width:\s*1[5-9]\d\dpx/s', $shared) === 1, 'wider wrapper for a page with a side nav');
    },

    'every icon used by a pane exists in the font the backoffice ships' => function () use ($email, $icons) {
        $font = file_get_contents($icons);
        preg_match_all('/<sb-section-pane icon="(fa-[a-z0-9-]+)"/', file_get_contents($email), $m);
        t_equal(3, count($m[1]), 'three icons');
        foreach ($m[1] as $icon) {
            t_true(strpos($font, '.' . $icon . ':before') !== false, "$icon is defined in the backoffice font-awesome.min.css");
        }
    },
];
