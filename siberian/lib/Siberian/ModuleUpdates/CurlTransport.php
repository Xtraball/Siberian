<?php

namespace Siberian\ModuleUpdates;

/**
 * Strict cURL transport: TLS verification on, TLS 1.2 minimum, https only, no redirects,
 * response size cap. Unlike \Siberian\License, certificate checks are never disabled here.
 */
class CurlTransport implements Transport
{
    /**
     * @inheritdoc
     */
    public function request($method, $url, array $headers = [], $body = null, $timeout = 10, $maxBytes = 5242880)
    {
        if (stripos($url, 'https://') !== 0) {
            throw new Exception('insecure_url', 'Module updates are only available over HTTPS.');
        }

        $responseHeaders = [];
        $received = 0;
        $content = '';
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$responseHeaders) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return strlen($line);
            },
            // Returning a short count aborts the transfer once the cap is passed.
            CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$content, &$received, $maxBytes) {
                $received += strlen($chunk);
                if ($received > $maxBytes) {
                    return 0;
                }
                $content .= $chunk;

                return strlen($chunk);
            },
        ]);
        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $ok = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return [
            'status' => $ok === false ? 0 : $status,
            'headers' => $responseHeaders,
            'body' => $ok === false ? '' : $content,
        ];
    }
}
