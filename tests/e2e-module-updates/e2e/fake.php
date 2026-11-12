<?php

// Fake SiberianCMS licence service and EDD store. Behaviour is driven by the key values:
//   sk-valid-*  valid Siberian licence      anything else: 400
//   lk-valid-*  valid module licence        lk-bad-*: invalid     lk-used-*: already used elsewhere
//   lk-fresh-*  inactive, gets activated
$host = $_SERVER['HTTP_HOST'] ?? '';
$path = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
header('Content-Type: application/json');
file_put_contents('php://stderr', date('H:i:s') . " $host " . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . "\n");

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

if (strpos($host, 'store.test') === 0) {
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

http_response_code(404);
echo '{}';
