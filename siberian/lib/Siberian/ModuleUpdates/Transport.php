<?php

namespace Siberian\ModuleUpdates;

/**
 * HTTP transport used by the module update flow only (the rest of Siberian keeps its own clients).
 */
interface Transport
{
    /**
     * @param string $method GET or POST
     * @param string $url https only
     * @param array $headers Raw header lines
     * @param string|null $body
     * @param int $timeout Seconds
     * @param int $maxBytes Hard cap on the response body
     * @return array ['status' => int (0 on network or TLS failure), 'headers' => lower-cased name => value, 'body' => string]
     */
    public function request($method, $url, array $headers = [], $body = null, $timeout = 10, $maxBytes = 5242880);
}
