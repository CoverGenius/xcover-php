<?php

namespace XCoverClient\Exceptions;

use Psr\Http\Message\ResponseInterface;
use XCoverClient\Response;

class ResponseException extends XCoverException
{
    /**
     * @var ResponseInterface The response that threw the exception.
     */
    protected ResponseInterface $response;

    /**
     * Creates a ResponseException.
     *
     * @param ResponseInterface $response - The response that generated this exception
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        $errorMessage = $this->getFromResponse('message');
        if (!$errorMessage) {
            $errorMessage = $this->getFromResponse('error', "{$response->getStatusCode()} error");
        }
        $errorCode = $this->getFromResponse('type', 'api_error');

        $message = sprintf("%s: %s", $errorCode, $errorMessage);

        parent::__construct($message, $response->getStatusCode());
    }

    /**
     * Checks isset and returns that or a default value.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getFromResponse($key, $default = null)
    {
        if ($this->response instanceof Response && $this->response->isJsonResponse()) {
            $responseData = $this->response->json();
            return isset($responseData[$key]) ? $responseData[$key] : $default;
        }

        return $default;
    }

    /**
     * Response getter
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
