<?php
/**
 * Backoffice: Settings > General is made of sections (side navigation) and each section saves only what it shows:
 * Your Platform, Location, Privacy and Licenses (the last two have nothing to edit), Your company, Your Applications,
 * Analytics (action buttons only). See docs/backoffice-ui.md.
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae';
$template = $root . '/modules/System/resources/design/desktop/backoffice/template/system/config/general.phtml';
$icons = $root . '/design/desktop/backoffice/css/font-awesome.min.css';

/**
 * @param string $template
 * @return array the raw text of each pane, in order
 */
$panes = function ($template) {
    return array_slice(explode('<sb-section-pane ', file_get_contents($template)), 1);
};

return [
    'the page is one side navigation of seven panes and no plain section is left' => function () use ($template) {
        $html = file_get_contents($template);
        t_equal(1, substr_count($html, '<sb-section-nav>'));
        t_equal(1, substr_count($html, '</sb-section-nav>'));
        t_equal(7, substr_count($html, '<sb-section-pane '));
        t_equal(7, substr_count($html, '</sb-section-pane>'));
        t_equal(0, substr_count($html, '<sb-section '), 'no plain section');
        t_equal(0, substr_count($html, 'ng-submit="save()"'), 'no page-wide save: it would post every section');
    },

    'each section saves exactly the settings it shows' => function () use ($template, $panes) {
        $expected = [
            0 => ['main_domain', 'platform_name'],
            1 => ['system_currency', 'system_default_language', 'system_timezone'],
            4 => ['company_address', 'company_country', 'company_name', 'company_phone', 'company_vat_number'],
            5 => ['export_enabled', 'import_enabled', 'system_publication_access_type'],
        ];
        $all = $panes($template);
        t_equal(7, count($all));
        $saved = [];
        foreach ($expected as $i => $codes) {
            t_equal(1, substr_count($all[$i], '<form '), "pane $i has its own form");
            t_equal(1, substr_count($all[$i], 'type="submit"'), "pane $i has a Save button");
            t_true(preg_match('/ng-submit="save\(\[([^\]]+)\]\)"/', $all[$i], $m) === 1, "pane $i scopes its save");
            preg_match_all("/'([a-z_]+)'/", $m[1], $given);
            preg_match_all('/ng-model="configs\.([a-z_]+)\./', $all[$i], $models);
            $given = $given[1];
            $shown = array_unique($models[1]);
            sort($given);
            sort($shown);
            t_equal(implode(',', $codes), implode(',', $given), "pane $i saves the expected settings");
            t_equal(implode(',', $codes), implode(',', $shown), "pane $i saves what it shows");
            $saved = array_merge($saved, $given);
        }
        t_equal(count($saved), count(array_unique($saved)), 'no setting is saved by two sections');
    },

    'sections with nothing to edit have no form and no Save button' => function () use ($template, $panes) {
        $all = $panes($template);
        foreach ([2 => 'Privacy', 3 => 'Licenses', 6 => 'Analytics'] as $i => $name) {
            t_equal(0, preg_match('/<form\b|type="submit"|ng-submit/', $all[$i]), "$name has nothing to save");
        }
        t_true(strpos($all[6], 'Compute_Analytics()') !== false && strpos($all[6], 'Compute_Analytics_For_Period()') !== false, 'the analytics actions are kept');
    },

    'every setting of the old page is still in a section' => function () use ($template) {
        $html = file_get_contents($template);
        foreach (['platform_name', 'main_domain', 'system_timezone', 'system_currency', 'system_default_language', 'company_name', 'company_address', 'company_country', 'company_phone', 'company_vat_number', 'system_publication_access_type', 'import_enabled', 'export_enabled'] as $code) {
            t_true(strpos($html, 'ng-model="configs.' . $code . '.value"') !== false, $code);
        }
        t_true(strpos($html, '{{ configs.siberiancms_key.value }}') !== false, 'licence key is displayed');
    },

    'every icon exists in the font the backoffice ships' => function () use ($template, $icons) {
        $font = file_get_contents($icons);
        preg_match_all('/<sb-section-pane\s+icon="(fa-[a-z0-9-]+)"/', file_get_contents($template), $m);
        t_equal(7, count($m[1]), 'seven icons');
        foreach ($m[1] as $icon) {
            t_true(strpos($font, '.' . $icon . ':before') !== false, "$icon is in the backoffice font-awesome.min.css");
        }
    },

    'the main domain is still validated server side when it is posted' => function () use ($root) {
        $php = file_get_contents($root . '/modules/System/Controller/Backoffice/Default.php');
        t_true(strpos($php, 'array_key_exists("main_domain", $data)') !== false, 'only checked when the section sends it');
    },
];
