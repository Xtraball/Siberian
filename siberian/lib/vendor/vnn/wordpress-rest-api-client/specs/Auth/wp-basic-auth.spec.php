<?php

use GuzzleHttp\Psr7\Request;
use Vnn\WpApiClient\Auth\WpBasicAuth;

describe(WpBasicAuth::class, function () {
    describe('addCredentials()', function () {
        it('should return a request with the proper Authorization header', function () {
            $auth = new WpBasicAuth('jim', 'hunter2');
            $request = new Request('GET', '/users');

            $newRequest = $auth->addCredentials($request);

            expect($newRequest)->to->be->instanceof(Request::class);
            expect($newRequest->getHeader('Authorization'))->to->equal([
                'Basic ' . base64_encode('jim:hunter2')
            ]);
        });
    });
});
