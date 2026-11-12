<?php
/**
 * Happy paths for the dependencies that carry security advisories, driven the
 * way first-party code drives them (WordpressApi, RssController, VestaCP\Client,
 * Notification\Slack, Cypher, Onesignal\Notification). A dependency bump is
 * iso-functional only if every one of these still passes unchanged.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

return [
    // guzzlehttp/guzzle + guzzlehttp/psr7 -------------------------------------
    'Guzzle GET with query and headers' => function () {
        $client = new GuzzleHttp\Client(['base_uri' => t_server()]);
        $res = $client->request('GET', '/echo', ['query' => ['a' => '1'], 'headers' => ['X-Test' => 'y']]);
        t_equal(200, $res->getStatusCode(), 'status');
        $json = json_decode((string) $res->getBody(), true);
        t_equal('GET', $json['method'], 'method');
        t_equal(['a' => '1'], $json['query'], 'query');
    },

    'Guzzle POST json and form' => function () {
        $client = new GuzzleHttp\Client(['base_uri' => t_server()]);
        $json = json_decode((string) $client->post('/echo', ['json' => ['k' => 'v']])->getBody(), true);
        t_equal('{"k":"v"}', $json['body'], 'json body');
        t_equal('application/json', $json['type'], 'json type');
        $form = json_decode((string) $client->post('/echo', ['form_params' => ['a' => 'b c']])->getBody(), true);
        t_equal('a=b+c', $form['body'], 'form body');
    },

    'Guzzle follows redirects' => function () {
        $client = new GuzzleHttp\Client(['base_uri' => t_server()]);
        $json = json_decode((string) $client->get('/redirect')->getBody(), true);
        t_equal(['from' => 'redirect'], $json['query'], 'redirected');
    },

    'Guzzle raises on HTTP errors' => function () {
        $client = new GuzzleHttp\Client(['base_uri' => t_server()]);
        try {
            $client->get('/status/500');
        } catch (GuzzleHttp\Exception\ServerException $e) {
            t_equal(500, $e->getResponse()->getStatusCode(), 'status');
            return;
        }
        throw new RuntimeException('expected ServerException');
    },

    'Guzzle async requests resolve' => function () {
        $client = new GuzzleHttp\Client(['base_uri' => t_server()]);
        $promises = ['a' => $client->getAsync('/echo?n=1'), 'b' => $client->getAsync('/echo?n=2')];
        // Promise::wait() works across promises 1.x and 2.x; the unwrap() helper was removed in 2.0.
        $results = ['a' => $promises['a']->wait(), 'b' => $promises['b']->wait()];
        t_equal(['1', '2'], [
            json_decode((string) $results['a']->getBody(), true)['query']['n'],
            json_decode((string) $results['b']->getBody(), true)['query']['n'],
        ], 'async results');
    },

    'psr7 Uri, Request and Stream' => function () {
        $uri = new GuzzleHttp\Psr7\Uri('https://user@example.com:8443/p/a?q=1#f');
        t_equal('example.com', $uri->getHost(), 'host');
        t_equal(8443, $uri->getPort(), 'port');
        t_equal('https://example.com:8443/x?q=1#f', (string) $uri->withUserInfo('')->withPath('/x'), 'with*');
        $req = new GuzzleHttp\Psr7\Request('POST', $uri, ['X-A' => '1'], 'body');
        t_equal('1', $req->getHeaderLine('X-A'), 'header');
        t_equal('body', (string) $req->getBody(), 'body');
        // Utils::streamFor exists from psr7 1.7; older releases only ship stream_for().
        $stream = class_exists('GuzzleHttp\\Psr7\\Utils')
            ? GuzzleHttp\Psr7\Utils::streamFor('hello')
            : GuzzleHttp\Psr7\stream_for('hello');
        t_equal('hel', $stream->read(3), 'read');
        t_equal('lo', $stream->getContents(), 'rest');
    },

    // vnn/wordpress-rest-api-client via GuzzleAdapter (Wordpress2\WordpressApi) --
    'WordPress REST client lists pages' => function () {
        $client = new Vnn\WpApiClient\WpClient(
            new Vnn\WpApiClient\Http\GuzzleAdapter(new GuzzleHttp\Client()),
            t_server()
        );
        $pages = $client->pages()->get(null, ['per_page' => 2]);
        t_equal(2, count($pages), 'count');
        t_equal('Hello pages', $pages[0]['title']['rendered'], 'title');
    },

    // debril/feed-io (Rss controller) -----------------------------------------
    'feed-io reads an RSS feed' => function () {
        $result = FeedIo\Factory::create()->getFeedIo()->read(t_server() . '/rss.xml');
        $titles = [];
        foreach ($result->getFeed() as $item) {
            $titles[] = $item->getTitle();
        }
        t_equal(['First post', 'Second post'], $titles, 'items');
        t_equal('Upgrade feed', $result->getFeed()->getTitle(), 'feed title');
    },

    // fabpot/goutte + symfony browser-kit/dom-crawler (VestaCP\Client) --------
    'Goutte scrapes and submits a form' => function () use ($siberian) {
        require_once $siberian . '/lib/Siberian/VestaCP/Client.php';
        $client = new Siberian\VestaCP\Client();
        $crawler = $client->_request('GET', t_server() . '/vesta/login');
        t_equal('Login', $crawler->filter('h1')->text(), 'login page');
        $form = $crawler->selectButton('Sign in')->form(['user' => 'admin', 'password' => 'pw']);
        $after = $client->submit($form);
        t_equal('Welcome admin', $after->filter('#who')->text(), 'after submit');
    },

    // maknz/slack (Notification\Slack) ----------------------------------------
    // The full payload is pinned so that swapping maknz/slack for SlackWebhook
    // is provably byte-identical on the wire. While maknz is still installed
    // both clients are checked against the same expectation.
    'Slack webhook payload' => function () use ($siberian) {
        require_once $siberian . '/lib/Siberian/Notification/SlackWebhook.php';
        $clients = ['Siberian\\Notification\\SlackWebhook'];
        if (class_exists('Maknz\\Slack\\Client')) {
            $clients[] = 'Maknz\\Slack\\Client';
        }
        foreach ($clients as $class) {
            @unlink($f = sys_get_temp_dir() . '/upgrade-slack-payload.txt');
            $slack = new $class(t_server() . '/slack', ['username' => 'bot', 'channel' => '#ops', 'link_names' => true]);
            $slack->send('deploy done');
            t_equal([
                'text' => 'deploy done',
                'channel' => '#ops',
                'username' => 'bot',
                'link_names' => 1,
                'unfurl_links' => false,
                'unfurl_media' => true,
                'mrkdwn' => true,
                'attachments' => [],
            ], json_decode(file_get_contents($f), true), "$class payload");

            @unlink($f);
            $slack->to('#other')->send('héllo');
            $payload = json_decode(file_get_contents($f), true);
            t_equal('#other', $payload['channel'], "$class to()");
            t_equal('héllo', $payload['text'], "$class unicode text");
            t_true(strpos(file_get_contents($f), 'héllo') !== false, "$class unescaped unicode on the wire");
        }
    },

    // phpseclib (Siberian\Cypher, IosAutopublish) -----------------------------
    // First-party code only uses RSA PKCS#1 v1.5 encrypt (public key) and decrypt
    // (private key, optional passphrase). The counterpart is openssl itself, which
    // is what the autopublish API on the other end effectively is, so a library
    // swap must stay interoperable in both directions and for every key format.
    'Cypher interoperates with openssl for each key format' => function () use ($siberian) {
        require_once $siberian . '/lib/Siberian/Cypher.php';
        $dir = sys_get_temp_dir();
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $details = openssl_pkey_get_details($key);
        openssl_pkey_export($key, $privatePkcs8);
        openssl_pkey_export($key, $privateEncrypted, 's3cret');

        $der = function ($tag, $body) {
            $len = strlen($body);
            $l = $len < 128 ? chr($len) : (($len < 256) ? "\x81" . chr($len) : "\x82" . pack('n', $len));
            return chr($tag) . $l . $body;
        };
        $int = function ($bin) use ($der) {
            return $der(0x02, (ord($bin[0]) & 0x80 ? "\x00" : '') . $bin);
        };
        $n = $details['rsa']['n'];
        $e = $details['rsa']['e'];
        $pkcs1Public = "-----BEGIN RSA PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der(0x30, $int($n) . $int($e))), 64, "\n")
            . "-----END RSA PUBLIC KEY-----\n";
        $mpint = function ($bin) {
            return pack('N', strlen($bin) + (ord($bin[0]) & 0x80 ? 1 : 0)) . (ord($bin[0]) & 0x80 ? "\x00" : '') . $bin;
        };
        $openssh = 'ssh-rsa ' . base64_encode(pack('N', 7) . 'ssh-rsa' . $mpint($e) . $mpint($n)) . " upgrade-test\n";

        $publics = ['pkcs8' => $details['key'], 'pkcs1' => $pkcs1Public, 'openssh' => $openssh];
        foreach ($publics as $format => $public) {
            file_put_contents("$dir/upgrade-pub-$format.pem", $public);
            // Cypher -> openssl
            $cipher = Siberian\Cypher::cypher("$dir/upgrade-pub-$format.pem", 'license-payload');
            t_true(openssl_private_decrypt(base64_decode($cipher), $plain, $key, OPENSSL_PKCS1_PADDING), "$format: openssl decrypts Cypher output");
            t_equal('license-payload', $plain, "$format: plaintext");
        }

        // openssl -> Cypher, with and without a passphrase
        openssl_public_encrypt('from-the-server', $enc, $details['key'], OPENSSL_PKCS1_PADDING);
        file_put_contents("$dir/upgrade-priv.pem", $privatePkcs8);
        t_equal('from-the-server', Siberian\Cypher::decypher("$dir/upgrade-priv.pem", base64_encode($enc)), 'decypher plain key');
        // Passphrase-protected key in the traditional PEM format (Proc-Type/DEK-Info),
        // built by hand because openssl_pkey_export only writes encrypted PKCS#8.
        $rsa = $details['rsa'];
        $privateDer = $der(0x30, $int("\x00") . $int($rsa['n']) . $int($rsa['e']) . $int($rsa['d']) . $int($rsa['p'])
            . $int($rsa['q']) . $int($rsa['dmp1']) . $int($rsa['dmq1']) . $int($rsa['iqmp']));
        $iv = str_repeat("\x07", 16);
        $k1 = md5('s3cret' . substr($iv, 0, 8), true);
        $encrypted = openssl_encrypt($privateDer, 'aes-128-cbc', $k1, OPENSSL_RAW_DATA, $iv);
        file_put_contents("$dir/upgrade-priv-enc.pem", "-----BEGIN RSA PRIVATE KEY-----\nProc-Type: 4,ENCRYPTED\nDEK-Info: AES-128-CBC,"
            . strtoupper(bin2hex($iv)) . "\n\n" . chunk_split(base64_encode($encrypted), 64, "\n") . "-----END RSA PRIVATE KEY-----\n");
        t_equal('from-the-server', Siberian\Cypher::decypher("$dir/upgrade-priv-enc.pem", base64_encode($enc), 's3cret'), 'decypher encrypted key');

        // Cypher -> Cypher
        $round = Siberian\Cypher::cypher("$dir/upgrade-pub-pkcs8.pem", 'unicode héllo ✓');
        t_equal('unicode héllo ✓', Siberian\Cypher::decypher("$dir/upgrade-priv.pem", $round), 'round trip');
    },

    // maximebf/debugbar (Siberian\Debug) --------------------------------------
    'DebugBar collects and renders' => function () {
        $bar = new DebugBar\StandardDebugBar();
        $bar['messages']->info('hello debugbar');
        $data = $bar->getData();
        t_equal('hello debugbar', $data['messages']['messages'][0]['message'], 'message');
        $renderer = $bar->getJavascriptRenderer('/assets');
        t_true(strpos($renderer->renderHead(), '<script') !== false, 'head');
        t_true(strpos($renderer->render(), 'phpdebugbar') !== false, 'body');
    },

    // lib/onesignal/vendor (Push2\Model\Onesignal\Notification) ---------------
    'OneSignal client lists notifications' => function () use ($siberian) {
        require_once $siberian . '/lib/onesignal/vendor/autoload.php';
        $config = onesignal\client\Configuration::getDefaultConfiguration()
            ->setAppKeyToken('test-token')
            ->setHost(t_server() . '/api/v1');
        $api = new onesignal\client\api\DefaultApi(new GuzzleHttp\Client(), $config);
        $res = $api->getNotifications('app-id', 50, 0, 1);
        t_equal(1, $res->getTotalCount(), 'total');
        t_equal('abc-123', $res->getNotifications()[0]->getId(), 'id');
    },
];
