<?php

use Vnn\WpApiClient\Endpoint\Media;
use Vnn\WpApiClient\WpClient;
use Vnn\WpApiClient\Http\GuzzleAdapter;
use Vnn\WpApiClient\Auth\WpBasicAuth;
use Prophecy\Argument;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

describe(Media::class, function () {
    beforeEach(function () {
        $this->wpClient = $this->getProphet()->prophesize(WpClient::class);
        $this->mediaEndpoint = new Media($this->wpClient->reveal());
    });

    context('when the file to be uploaded is not found', function () {
        xit('should throw a Runtime Exception for a missing remote file', function () {
            $imgUrl = 'http://example.com/img/baby-squirrel-eating-pizza.jpg';
            $contentType = 'image/jpeg';
            set_error_handler(function (int $errno, string $errstr, string $errfile) {
                expect($errno)->to->equal(E_WARNING);
                expect(strpos($errstr, '404 Not Found'))->to->not->equal(false);
            });

            expect(
                [
                    $this->mediaEndpoint,
                    'upload'
                ]
            )
                ->with($imgUrl, [], $contentType)
                ->to->throw(RuntimeException::class);

            restore_error_handler();
        });

        it('should throw a Runtime Exception for a missing local file', function () {
            $filename = realpath(dirname('../')) . '/file-does-not-exist.txt';

            set_error_handler(function (int $errno, string $errstr, string $errfile) {
                expect($errno)->to->equal(E_WARNING);
                expect(strpos($errstr, ' No such file or directory'))->to->not->equal(false);
            });

            expect(
                [
                    $this->mediaEndpoint,
                    'upload'
                ]
            )
                ->with($filename)
                ->to->throw(RuntimeException::class);

            restore_error_handler();
        });
    });

    context('when the file to be upload exists', function () {
        it('should attempt to create a new media item using a POST request', function () {
            // mocking the response...
            $streamResponse = $this->getProphet()->prophesize(StreamInterface::class);
            $streamResponse->getContents()->willReturn(json_encode(['id' => 32, 'date' => (new DateTime())->format('c')]))
                ->shouldBeCalled();

            $response = $this->getProphet()->prophesize(ResponseInterface::class);
            $response->hasHeader('Content-Type')->willReturn(true)->shouldBeCalled();
            $response->getHeader('Content-Type')->willReturn(['application/json'])->shouldBeCalled();
            $response->getBody()->willReturn($streamResponse->reveal());

            $this->wpClient
                ->send(Argument::that(function($arg) {
                    return ($arg instanceof Request) &&
                        $arg->getHeader('Content-Type') == ['text/plain'] &&
                        $arg->getHeader('Content-Disposition') == ['attachment; filename="README.md"'] &&
                        $arg->getMethod() == 'POST'
                    ;
                }))
                ->willReturn($response->reveal())
                ->shouldBeCalled();

            $filename = realpath(dirname('../')) . '/README.md';
            $response = $this->mediaEndpoint->upload($filename);
            expect($response['id'])->to->equal(32);
        });
    });

    afterEach(function () {
        $this->getProphet()->checkPredictions();
    });
});
