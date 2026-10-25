<?php
/**
 * #833: TinyURL is called over https first, http stays as a fallback. Driven through Zend_Http_Client with a
 * scripted adapter, no network.
 */
class T833Adapter extends Zend_Http_Client_Adapter_Test
{
    /** @var array scheme of every connection attempt */
    public $attempts = [];
    /** @var array schemes that refuse to connect */
    public $failing = [];

    public function connect($host, $port = 80, $secure = false)
    {
        $scheme = $secure ? 'https' : 'http';
        $this->attempts[] = $scheme;
        if (in_array($scheme, $this->failing, true)) {
            throw new Zend_Http_Client_Adapter_Exception("$scheme refused");
        }
        parent::connect($host, $port, $secure);
    }
}

$t833 = function (array $failing, string $body = 'https://tinyurl.com/abc') {
    $adapter = new T833Adapter();
    $adapter->failing = $failing;
    $adapter->setResponse("HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\n\r\n" . $body);
    $service = new Zend_Service_ShortUrl_TinyUrlCom();
    $service->setHttpClient(new Zend_Http_Client(null, ['adapter' => $adapter]));
    return [$service, $adapter];
};

return [
    '833 shorten uses https and does not touch http when https works' => function () use ($t833) {
        [$service, $adapter] = $t833([]);
        t_equal('https://tinyurl.com/abc', $service->shorten('https://example.com/a/very/long/url'), 'short url');
        t_equal(['https'], $adapter->attempts, 'only https was used');
    },

    '833 shorten falls back to http when https is refused' => function () use ($t833) {
        [$service, $adapter] = $t833(['https'], 'http://tinyurl.com/abc');
        t_equal('http://tinyurl.com/abc', $service->shorten('https://example.com/a/very/long/url'), 'short url');
        t_equal(['https', 'http'], $adapter->attempts, 'https first, then http');
    },

    '833 shorten still fails when both schemes are refused' => function () use ($t833) {
        [$service, $adapter] = $t833(['https', 'http']);
        try {
            $service->shorten('https://example.com/a/very/long/url');
        } catch (Zend_Http_Client_Exception $e) {
            t_equal(['https', 'http'], $adapter->attempts, 'both were tried');
            return;
        }
        throw new RuntimeException('expected the request error to surface');
    },

    '833 an invalid url is still rejected before any request' => function () use ($t833) {
        [$service, $adapter] = $t833([]);
        try {
            $service->shorten('not a url');
        } catch (Zend_Service_ShortUrl_Exception $e) {
            t_equal([], $adapter->attempts, 'no request was made');
            return;
        }
        throw new RuntimeException('expected an exception for an invalid url');
    },

    '833 unshorten still rejects links of another host, before any request' => function () {
        foreach (['https://evil.example/abc', 'http://evil.example/abc'] as $foreign) {
            try {
                (new Zend_Service_ShortUrl_TinyUrlCom())->unshorten($foreign);
            } catch (Zend_Service_ShortUrl_Exception $e) {
                continue;
            }
            throw new RuntimeException("$foreign must be rejected");
        }
    },
];
