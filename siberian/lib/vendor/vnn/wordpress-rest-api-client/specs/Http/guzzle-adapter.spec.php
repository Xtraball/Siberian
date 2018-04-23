<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;
use Vnn\WpApiClient\Http\GuzzleAdapter;

describe(GuzzleAdapter::class, function () {
    describe('makeUri()', function () {
        it('should return a Guzzle Uri object wrapping the string', function () {
            $adapter = new GuzzleAdapter();
            $uri = $adapter->makeUri('http://lol.com');

            expect($uri)->to->be->instanceof(UriInterface::class);
            expect($uri->getScheme())->to->equal('http');
            expect($uri->getHost())->to->equal('lol.com');
        });
    });

    describe('send()', function () {
        it('should pass the request off to Guzzle and return the response', function () {
            $client = $this->getProphet()->prophesize(Client::class);
            $adapter = new GuzzleAdapter($client->reveal());

            $request = new Psr7\Request('GET', 'foo.com');
            $expectedResponse = new Psr7\Response();

            $client->send($request)->willReturn($expectedResponse)->shouldBeCalled();

            $response = $adapter->send($request);

            expect($response)->to->equal($expectedResponse);
        });
    });
});
