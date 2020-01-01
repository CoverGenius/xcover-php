<?php

namespace XCoverClient\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use XCoverClient\Response;

/**
 * Class AuthMiddleware
 *
 * @package XCover\Middleware
 */
class JsonResponseMiddleware
{
    /**
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (
            RequestInterface $request,
            array $options
        ) use ($handler) {
            $promise = $handler($request, $options);
            return $promise->then(
                function (ResponseInterface $response) {
                    return new Response(
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $response->getBody(),
                        $response->getProtocolVersion(),
                        $response->getReasonPhrase(),
                    );
                }
            );
        };
    }
}