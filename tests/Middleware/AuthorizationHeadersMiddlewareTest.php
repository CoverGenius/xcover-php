<?php

namespace XCoverClient\Tests\Middleware;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use XCoverClient\Middleware\AuthMiddleware;
use XCoverClient\Tests\BaseTestCase as TestCase;

class AuthorizationHeadersMiddlewareTest extends TestCase
{
    const ENDPOINT = 'https://example.com/some-fake-uri';

    /** @test */
    public function it_can_invoke_authorization_header_to_the_handler_instance()
    {
        $mockHandler = new MockHandler([
            new Response(200)
        ]);

        $stack = new HandlerStack($mockHandler);

        $authorizationOptions = array_merge(
            ['apiKey' => 'some-api-key'],
            ['apiSecret' => 'some-api-secret'],
        );

        $stack->push(new AuthMiddleware($authorizationOptions));

        $handler = $stack->resolve();

        $request = new Request('GET', static::ENDPOINT);

        $handler($request, []);

        $requestHeaders = $mockHandler->getLastRequest()->getHeaders();

        $this->assertArrayHasKey('Authorization', $requestHeaders);
        $authorizationHeader = $requestHeaders['Authorization'][0];

        $this->assertTrue(stripos($authorizationHeader, 'Signature keyId="some-api-key",algorithm="hmac-' . AuthMiddleware::SIGNATURE_ALGORITHM . '",') !== false);
        $this->assertTrue(stripos($authorizationHeader, 'signature="') !== false);
    }
}