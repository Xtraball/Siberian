<?php

global $_config;

// When you need to catch fatal errors create the corresponding config line `$_config["handle_fatal_errors"] = true;`!
if (isset($_config['handle_fatal_errors']) &&
    $_config['handle_fatal_errors'] === true) {
    // Handle fatal errors!
    function shutdownFatalHandler()
    {
        $error = error_get_last();
        if ($error !== null) {
            ob_clean();
            http_response_code(400);

            $payload = [
                'error' => true,
                'fullError' => $error,
                'message' => 'ERROR: ' . str_replace("\n", ' - ', $error['message']),
            ];

            file_put_contents(
                __DIR__ . '/../../var/tmp/fatal.log',
                date('d/m/Y H:i:s') . ': ' . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL,
                FILE_APPEND);
        } else {
            file_put_contents(
                __DIR__ . '/../../var/tmp/fatal.log',
                date('d/m/Y H:i:s') . ':  shutdownFatalHandler' . $error . PHP_EOL,
                FILE_APPEND);
        }
    }

    // Handle fatal errors!
    register_shutdown_function('shutdownFatalHandler');
} else {
    // Handling max memory size issues only!
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error !== null) {
            if (preg_match('/Allowed memory size/im', $error['message'])) {
                ob_clean();
                http_response_code(400);

                $payload = [
                    'error' => true,
                    'fullError' => $error,
                    'message' => 'ERROR: ' . str_replace("\n", ' - ', $error['message']),
                ];

                file_put_contents(
                    __DIR__ . '/../../var/tmp/fatal.log',
                    date('d/m/Y H:i:s') . ': ' . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL,
                    FILE_APPEND);
            }
        }
    });
}