<?php
/**
 * Router for the throwaway `php -S` server the happy-path tests talk to. It
 * stands in for WordPress, an RSS feed, a Slack webhook, a VestaCP login page
 * and the OneSignal API, so tests exercise real HTTP without leaving the box.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$body = file_get_contents('php://input');

function respond($data, $status = 200, $type = 'application/json', array $headers = [])
{
    http_response_code($status);
    header('Content-Type: ' . $type);
    foreach ($headers as $k => $v) {
        header("$k: $v");
    }
    echo is_string($data) ? $data : json_encode($data);
    exit;
}

switch (true) {
    case $path === '/echo':
        respond([
            'method' => $_SERVER['REQUEST_METHOD'],
            'query' => $_GET,
            'body' => $body,
            'auth' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
            'type' => $_SERVER['CONTENT_TYPE'] ?? null,
            'agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

    case $path === '/redirect':
        header('Location: /echo?from=redirect', true, 302);
        exit;

    case $path === '/status/500':
        respond(['error' => 'boom'], 500);

    case preg_match('#^/wp-json/wp/v2/(pages|posts|categories|media)$#', $path, $m):
        respond([
            ['id' => 1, 'slug' => 'hello', 'title' => ['rendered' => 'Hello ' . $m[1]]],
            ['id' => 2, 'slug' => 'world', 'title' => ['rendered' => 'World ' . $m[1]]],
        ], 200, 'application/json', ['X-WP-Total' => '2', 'X-WP-TotalPages' => '1']);

    case $path === '/rss.xml':
        respond(
            '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>'
            . '<title>Upgrade feed</title><link>http://example.test/</link><description>d</description>'
            . '<item><title>First post</title><link>http://example.test/1</link><guid>1</guid>'
            . '<pubDate>Mon, 01 Jan 2024 10:00:00 +0000</pubDate><description>one</description></item>'
            . '<item><title>Second post</title><link>http://example.test/2</link><guid>2</guid>'
            . '<pubDate>Tue, 02 Jan 2024 10:00:00 +0000</pubDate><description>two</description></item>'
            . '</channel></rss>',
            200,
            'application/rss+xml'
        );

    case $path === '/slack':
        file_put_contents(sys_get_temp_dir() . '/upgrade-slack-payload.txt', $body);
        respond('ok', 200, 'text/plain');

    case $path === '/vesta/login' && $_SERVER['REQUEST_METHOD'] === 'GET':
        respond(
            '<html><body><h1>Login</h1><form method="post" action="/vesta/login">'
            . '<input name="user" value=""><input name="password" type="password" value="">'
            . '<input type="submit" value="Sign in"></form></body></html>',
            200,
            'text/html'
        );

    case $path === '/vesta/login':
        parse_str($body, $f);
        respond('<html><body><h1 id="who">Welcome ' . htmlspecialchars($f['user'] ?? '?') . '</h1></body></html>', 200, 'text/html');

    case $path === '/api/v1/notifications':
        respond(['total_count' => 1, 'offset' => 0, 'limit' => 50, 'notifications' => [[
            'id' => 'abc-123', 'successful' => 5, 'failed' => 0, 'converted' => 0, 'remaining' => 0,
            'queued_at' => 1700000000, 'send_after' => 1700000000, 'completed_at' => 1700000001,
            'contents' => ['en' => 'Hello from OneSignal'],
        ]]]);

    default:
        respond(['error' => 'not found', 'path' => $path], 404);
}
