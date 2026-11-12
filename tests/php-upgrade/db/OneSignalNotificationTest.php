<?php
/**
 * Push2\Model\Onesignal\Notification (tier 1: real core classes) against a fake OneSignal API (fixtures/router.php, /api/v1):
 * what regularPush() builds from a message, what is really sent, the answers of the API, the
 * import of devices and the error paths.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

// The Push2 files require their libraries through path() at load time
Core_Model_Directory::setBasePath($siberian);

require_once $siberian . '/lib/onesignal/vendor/autoload.php';
require_once $siberian . '/lib/Siberian/Hook.php';
require_once $siberian . '/lib/Siberian/UUID.php';
require_once $siberian . '/app/sae/modules/Push2/Model/Onesignal/Text.php';
require_once $siberian . '/app/sae/modules/Push2/Model/Onesignal/Targets/AbstractTarget.php';
foreach (['Android', 'Ios', 'Web', 'Segment', 'Player'] as $target) {
    require_once $siberian . '/app/sae/modules/Push2/Model/Onesignal/Targets/' . $target . '.php';
}
require_once $siberian . '/app/sae/modules/Push2/Model/Onesignal/Notification.php';

use Push2\Model\Onesignal\Notification;
use Push2\Model\Onesignal\Targets\Android;
use Push2\Model\Onesignal\Targets\Ios;
use Push2\Model\Onesignal\Targets\Player;
use Push2\Model\Onesignal\Targets\Segment;
use Push2\Model\Onesignal\Targets\Web;

/** The real Message model on the real core base classes of the tier 1 harness (no database is touched). */
require_once $siberian . '/app/sae/modules/Push2/Model/Onesignal/Message.php';

/** A message as the scheduler builds it, from plain values. */
function t_message(array $v = [])
{
    $message = new Push2\Model\Onesignal\Message();
    $v += [
        'title' => 'Hello', 'body' => 'World', 'targets' => [new Segment('Subscribed Users')],
    ];
    foreach ($v as $key => $value) {
        $message->setData($key, $value);
    }

    return $message;
}

$notification = function () {
    onesignal\client\Configuration::getDefaultConfiguration()->setHost(t_server() . '/api/v1');

    return new Notification('app-id-1', 'key-1');
};

$sent = function () {
    return json_decode((string) @file_get_contents(sys_get_temp_dir() . '/upgrade-onesignal-notification.json'), true);
};

return [
    'regularPush sets the app, the heading and the body, decoded for the phone' => function () use ($notification) {
        $n = $notification();
        $n->regularPush(t_message(['title' => 'Caf&eacute; &amp; Co', 'body' => 'It&#039;s on']));
        t_equal('app-id-1', $n->notification->getAppId());
        t_equal('Café & Co', $n->notification->getHeadings()->getEn());
        t_equal("It's on", $n->notification->getContents()->getEn());
        t_equal(10, $n->notification->getPriority());
        t_equal('Increase', $n->notification->getIosBadgeType());
    },
    'every target type is mapped to its OneSignal field' => function () use ($notification) {
        $n = $notification();
        $n->regularPush(t_message(['targets' => [
            new Android(['a1', 'a2']), new Ios('i1'), new Web(['w1']), new Segment('VIP'), new Player(['p1']),
        ]]));
        t_equal(['a1', 'a2'], $n->notification->getIncludeAndroidRegIds());
        t_equal(['i1'], $n->notification->getIncludeIosTokens());
        t_equal(['w1'], $n->notification->getIncludeChromeRegIds());
        t_equal(['VIP'], $n->notification->getIncludedSegments());
        t_equal(['p1'], $n->notification->getIncludePlayerIds());
    },
    'a geolocated push uses a location filter and no target' => function () use ($notification) {
        $n = $notification();
        $n->regularPush(t_message(['use_location' => true, 'radius' => 500, 'latitude' => 48.85, 'longitude' => 2.35]));
        $filter = $n->notification->getFilters()[0];
        t_equal('location', $filter->field);
        t_equal([500, 48.85, 2.35], [$filter->radius, $filter->lat, $filter->long]);
        t_equal(null, $n->notification->getIncludedSegments());
    },
    'a cover picture and an action link add the data for the app' => function () use ($notification) {
        $n = $notification();
        $n->regularPush(t_message(['big_picture_url' => 'https://x/p.jpg', 'open_url' => true, 'feature_url' => '/app/feature/index/value_id/3']));
        $data = $n->notification->getData();
        t_equal('/app/feature/index/value_id/3', $data['action_value']);
        t_equal('https://x/p.jpg', $data['cover']);
        t_equal(false, $data['open_webview']);
        t_equal(true, $n->notification->getContentAvailable());

        $n = $notification();
        $n->regularPush(t_message(['open_url' => true, 'feature_url' => 'https://example.com/page']));
        t_equal(true, $n->notification->getData()['open_webview']);
    },
    'without cover and link no data is added' => function () use ($notification) {
        $n = $notification();
        $n->regularPush(t_message());
        t_equal(null, $n->notification->getData());
    },
    'sendNotification posts the notification and returns the answer of OneSignal' => function () use ($notification, $sent) {
        $n = $notification();
        $n->regularPush(t_message(['title' => 'Sale']));
        $result = $n->sendNotification();
        t_equal('new-notification-id', $result->getId());
        t_equal(3, $result->getRecipients());
        $body = $sent();
        t_equal('app-id-1', $body['app_id']);
        t_equal('Sale', $body['headings']['en']);
        t_equal(['Subscribed Users'], $body['included_segments']);
    },
    'the answer of OneSignal for the same external id comes back (idempotence key)' => function () use ($notification, $sent) {
        $n = $notification();
        $n->regularPush(t_message());
        $result = $n->sendNotification();
        t_equal($n->notification->getExternalId(), $sent()['external_id']);
        t_true(preg_match('/^[0-9a-f-]{36}$/', $n->notification->getExternalId()) === 1, 'uuid');
    },
    'a refused notification raises the ApiException with the status and the body' => function () use ($notification) {
        $n = $notification();
        $n->regularPush(t_message(['title' => 'reject-me']));
        $code = null;
        try {
            $n->sendNotification();
        } catch (onesignal\client\ApiException $e) {
            $code = $e->getCode();
            t_true(strpos((string) $e->getResponseBody(), 'not subscribed') !== false, 'body kept');
        }
        t_equal(400, $code);
    },
    'fetchLatestNotifications reads the list with the app id of the instance' => function () use ($notification) {
        $res = $notification()->fetchLatestNotifications();
        t_equal('abc-123', $res->getNotifications()[0]->getId());
    },
    'importDevices counts the devices created and skips the refused ones' => function () use ($notification) {
        @unlink(sys_get_temp_dir() . '/upgrade-onesignal-players.log');
        $android = [
            ['registration_id' => 'ok-android', 'customer_id' => 7, 'app_id' => 12, 'device_uid' => 'u1'],
            ['registration_id' => 'bad-token', 'customer_id' => null, 'app_id' => 12, 'device_uid' => 'u2'],
        ];
        $ios = [
            ['device_token' => 'ok-ios', 'device_model' => 'iPhone', 'device_version' => '18.1', 'customer_id' => null, 'app_id' => 12, 'device_uid' => 'u3'],
        ];
        t_equal(2, $notification()->importDevices($android, $ios));
        $lines = array_map(function ($l) {
            return json_decode($l, true);
        }, file(sys_get_temp_dir() . '/upgrade-onesignal-players.log', FILE_IGNORE_NEW_LINES));
        t_equal('os_customer_12_7', $lines[0]['external_user_id']);
        t_equal(1, $lines[0]['device_type']);
        t_equal('os_anonymous_12_u3', $lines[1]['external_user_id']);
        t_equal(0, $lines[1]['device_type']);
    },
    'a hook that returns no notification must not leave the push without one' => function () use ($notification) {
        // listeners of push.message.android.parsed receive and return ['notification' => ..., 'application' => ...]
        \Siberian\Hook::listen('push.message.android.parsed', 'sib_test_bad_listener', function ($payload) {
            return [];
        });
        $n = $notification();
        $n->regularPush(t_message());
        t_true($n->notification !== null, 'the notification built by Siberian was thrown away');
    },
];
