<?php
/**
 * Dashboard services: the cron, the required extensions and the optional binaries are status chips with a details
 * modal. The cron state is whether it runs: an error kept in its history is information, it never turns the chip red.
 */
$root = dirname(__DIR__, 3) . '/siberian/app/sae/design/desktop/backoffice/';
$template = file_get_contents($root . 'template/backoffice/index/index.phtml');
$js = file_get_contents($root . 'js/controllers/homepage.js');

return [
    'the three rows are chips with a details modal' => function () use ($template) {
        t_true(strpos($template, 'class="svc-strip"') !== false, 'the chips are there');
        t_true(strpos($template, 'id="services-details"') !== false, 'with their details modal');
        t_true(strpos($template, 'openServices()') !== false, 'a chip opens it');
        t_equal(0, preg_match('/<td>\s*<\?php echo __\("Required extensions"\) \?>/', $template), 'no table row of the required extensions');
        t_equal(0, preg_match('/<td>\s*<\?php echo __\("Cron scheduler"\) \?>\s*<\/td>/', $template), 'no table row of the cron');
    },

    'what the cron can do is still reachable' => function () use ($template) {
        foreach (["clear('locks'", "clear('cron_error')", 'showCronModal()', 'backoffice/advanced_cron'] as $needle) {
            t_true(strpos($template, $needle) !== false, "$needle is in the template");
        }
        t_true(strpos($template, 'cron_error.full') !== false, 'the last error is shown');
        t_true(strpos($template, 'ng-bind-html="services.cron_error.full"') !== false, 'with its full text');
    },

    'a cron error does not change the state of the cron' => function () use ($js) {
        $start = strpos($js, '$scope.cronState = function');
        t_true($start !== false, 'cronState() exists');
        $body = substr($js, $start, strpos($js, "\n    };", $start) - $start);
        t_true(strpos($body, 'cron_error') === false, 'cronState() does not read the errors');
        foreach (['cron_locks', 'cron_compat', 'cronActive', '.cron'] as $needle) {
            t_true(strpos($body, $needle) !== false, "it reads $needle");
        }
    },

    'Show more is a flag of an object: a link inside ng-if cannot set a variable of the controller' => function () use ($template) {
        t_equal(0, preg_match('/(?<![.\w])cron_error_show/', $template), 'no bare scope variable');
        t_true(substr_count($template, 'svc.errorShow') >= 3, 'the link and the details use svc.errorShow');
    },

    'a small slice of the storage bar is drawn at a minimum' => function () use ($js) {
        t_true(strpos($js, 'var MIN_SLICE = 4;') !== false, 'the minimum share is 4 percent');
        t_true(strpos($js, 'u.total_bytes;') !== false && strpos($js, 'quota_bytes > u.total_bytes') === false,
            'the bar is the distribution of the total, the quota does not rescale it');
    },

    'the new texts are in the catalog' => function () {
        $catalog = file_get_contents(dirname(__DIR__, 3) . '/siberian/languages/base/c_backoffice.po');
        foreach (['Running', 'last error', 'Details', 'Services', 'Cron errors', 'No error recorded.', 'Manage web space', 'Quota saved.'] as $text) {
            t_true(strpos($catalog, "msgctxt \"backoffice\"\nmsgid \"$text\"\n") !== false, "c_backoffice.po has: $text");
        }
    },
];
