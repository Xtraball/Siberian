<?php

use GuzzleHttp\Psr7\Request;
use Vnn\WpApiClient\Endpoint\AbstractWpEndpoint;
use Vnn\WpApiClient\WpClient;

describe(AbstractWpEndpoint::class, function () {
    describe('get()', function () {
        it('should make a GET request to the endpoint URL', function () {
            $client = $this->getProphet()->prophesize(WpClient::class);

            $request = new Request('GET', '/foo/55');
            $response = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"foo": "bar"}');

            $client->send($request)->willReturn($response)->shouldBeCalled();

            $endpoint = new FakeEndpoint($client->reveal());

            $data = $endpoint->get(55);
            expect($data)->to->equal(['foo' => 'bar']);
        });

        it('should make a GET request without any ID', function () {
            $client = $this->getProphet()->prophesize(WpClient::class);

            $request = new Request('GET', '/foo');
            $response = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"foo": "bar"}');

            $client->send($request)->willReturn($response)->shouldBeCalled();

            $endpoint = new FakeEndpoint($client->reveal());

            $data = $endpoint->get();
            expect($data)->to->equal(['foo' => 'bar']);
        });

        it('should make a GET request with parameters', function () {
            $client = $this->getProphet()->prophesize(WpClient::class);

            $request = new Request('GET', '/foo?bar=baz');
            $response = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"foo": "bar"}');

            $client->send($request)->willReturn($response)->shouldBeCalled();

            $endpoint = new FakeEndpoint($client->reveal());

            $data = $endpoint->get(null, ['bar'=>'baz']);
            expect($data)->to->equal(['foo' => 'bar']);
        });

    });

    describe('save()', function () {
        it('should make a POST request to the endpoint URL', function () {
            $client = $this->getProphet()->prophesize(WpClient::class);
            $response = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"foo": "bar"}');
            $client->send(\Prophecy\Argument::type(Request::class))->willReturn($response)->shouldBeCalled();

            $endpoint = new FakeEndpoint($client->reveal());

            $data = $endpoint->save(['foo' => 'bar']);
            expect($data)->to->equal(['foo' => 'bar']);
        });
    });

    afterEach(function () {
        $this->getProphet()->checkPredictions();
    });
});

class FakeEndpoint extends AbstractWpEndpoint {
    public function getEndpoint() {
        return '/foo';
    }
}
