<?php

namespace XCoverClient;

use GuzzleHttp\Psr7\Response as BaseResponse;

/**
 * Extends Guzzle response and adds json function for convenience.
 *
 * @package XCoverClient
 */
class Response extends BaseResponse
{
    /**
     * Cached json
     *
     * @var array
     */
    protected $json;

    public function json()
    {
        if ($this->json) {
            return $this->json;
        }

        $body = $this->getBody();
        if ($this->isJsonResponse()) {
            return $this->json = json_decode($body, true);
        }

        return null;
    }

    public function isJsonResponse()
    {
        return false !== strpos($this->getHeaderLine('Content-Type'), 'application/json');
    }
}