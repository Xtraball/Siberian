<?php

// Mock of every external service the specs may reach (see specs/rig/hosts.txt). Nothing leaves the rig.
// Behaviour of the SiberianCMS licence service and the EDD store is driven by the key values:
//   sk-valid-*  valid Siberian licence      anything else: 400
//   lk-valid-*  valid module licence        lk-bad-*: invalid     lk-used-*: already used elsewhere
//   lk-fresh-*  inactive, gets activated
// Any other host and path answers 200 with {"success":true}, an empty list or object, so a spec can exercise the code
// path that follows a successful call; a spec that needs a failure asks for it with a "mock-fail" query parameter.
$host = $_SERVER['HTTP_HOST'] ?? '';
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
header('Content-Type: application/json');
file_put_contents('php://stderr', date('H:i:s') . " $host " . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . "\n");

if (isset($_GET['mock-fail'])) {
    http_response_code((int) $_GET['mock-fail'] ?: 500);
    echo json_encode(['success' => false, 'error' => 'mock failure']);

    return;
}

if (strpos($host, 'services.test') === 0 && $path === '/api/external/license/check') {
    $body = json_decode((string) file_get_contents('php://input'), true);
    $key = strtolower((string) ($body['licenseKey'] ?? ''));
    if (strpos($key, 'sk-valid-') === 0) {
        echo json_encode(['success' => true, 'customer_email' => 'owner@example.test']);
    } else {
        http_response_code(400);
        echo 'Invalid license key.';
    }

    return;
}

if (strpos($host, 'store.test') === 0 || strpos($host, 'extensions.siberiancms.com') === 0) {
    $license = strtolower((string) ($_GET['license'] ?? ''));
    $action = $_GET['edd_action'] ?? '';
    if ($action === 'activate_license') {
        echo json_encode(['success' => true, 'license' => 'valid']);

        return;
    }
    if ($action === 'check_license') {
        if (strpos($license, 'lk-valid-') === 0) {
            echo json_encode(['success' => true, 'license' => 'valid', 'expires' => 'lifetime', 'site_count' => 1, 'customer_email' => 'owner@example.test']);
        } elseif (strpos($license, 'lk-fresh-') === 0) {
            echo json_encode(['success' => true, 'license' => 'inactive', 'expires' => 'lifetime', 'site_count' => 0]);
        } elseif (strpos($license, 'lk-used-') === 0) {
            echo json_encode(['success' => true, 'license' => 'site_inactive', 'expires' => 'lifetime', 'site_count' => 1]);
        } else {
            echo json_encode(['success' => false, 'license' => 'invalid', 'expires' => 'lifetime']);
        }

        return;
    }
}

// The Siberian update server (system updater, "remote" path). specs/updater drives it with two files in /tmp/specs-updater:
//   scenario  none (default) | available | empty-download | download-500 | check-500 | check-garbage
//   package   the archive served by get.php when the scenario is "available"
if (strpos($host, 'updates02.siberiancms.com') === 0) {
    $scenario = trim((string) @file_get_contents('/tmp/specs-updater/scenario')) ?: 'none';
    if ($path === '/check.php') {
        if ($scenario === 'check-500') {
            http_response_code(500);
            echo 'boom';
        } elseif ($scenario === 'check-garbage') {
            header('Content-Type: text/html');
            echo '<html>not json</html>';
        } elseif ($scenario === 'none') {
            echo json_encode(['success' => true, 'message' => 'Your system is up to date.']);
        } else {
            echo json_encode(['success' => true, 'version' => '9.9.9', 'filename' => 'SpecUpd-1.0.0.sib',
                'url' => 'https://updates02.siberiancms.com/get.php']);
        }

        return;
    }
    if ($path === '/get.php') {
        if ($scenario === 'download-500') {
            http_response_code(500);
        } elseif ($scenario === 'available') {
            header('Content-Type: application/octet-stream');
            readfile('/tmp/specs-updater/package');
        }

        return;
    }
}

// Geocoding and similar: the shape Siberian reads first, empty otherwise.
if (strpos($host, 'maps.googleapis.com') === 0) {
    echo json_encode(['status' => 'OK', 'results' => [['geometry' => ['location' => ['lat' => 48.85, 'lng' => 2.35]], 'formatted_address' => 'Mock, France']]]);

    return;
}

echo json_encode(['success' => true, 'mock' => $host, 'data' => [], 'results' => []]);
