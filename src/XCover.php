<?php

namespace XCoverClient;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;
use XCoverClient\Exceptions\ResponseException;
use XCoverClient\Exceptions\XCoverException;
use XCoverClient\Middleware\AuthMiddleware;
use XCoverClient\Middleware\JsonResponseMiddleware;

class XCover
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     * @param ClientInterface $client
     */
    public function __construct(Config $config, ClientInterface $client = null)
    {
        $this->config = $config;
        $this->client = $client ?: $this->createDefaultClient();
    }

    /**
     * Call XCover API
     *
     * @param string $method HTTP method.
     * @param string $url Relative URL to the partner's base URL (e.g. /quotes/).
     * @param int $expectedStatusCode Will cause a ResponseException if different response code is returned.
     * @param array $payload Request body if required.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function call($method, $url, $expectedStatusCode = null, $payload = [], $queryParams = [])
    {
        $options = [
            'body' => $payload ? \GuzzleHttp\json_encode($payload) : "{}",
            'query' => $queryParams,
        ];

        try {
            $response = $this->client->request($method, $this->makeUrl($url), $options);
        } catch (GuzzleException $e) {
            // Rethrow all Guzzle exceptions as XCoverException
            throw new XCoverException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() !== $expectedStatusCode) {
            throw new ResponseException($response);
        }

        return $response;
    }

    /**
     * Create quote.
     *
     * This method can be sued to send quotes for one or more insurance products. It creates a quote package and returns
     * it in response.
     *
     * @param array $payload Quote request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function createQuote(array $payload, array $queryParams = [])
    {
        $requestUri = '/quotes/';

        return $this->call('POST', $requestUri, 201, $payload, $queryParams);
    }

    /**
     * Get quote.
     *
     * Can be used to retrieve information about a quote package created using createQuote method.
     *
     * @param string $quotePackageId ID of the quote package created using createQuote method.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function getQuote($quotePackageId, array $queryParams = [])
    {
        $requestUri = "/quotes/{$quotePackageId}/";

        return $this->call('GET', $requestUri, 200, null, $queryParams);
    }

    /**
     * Update quote.
     *
     * Used to update quote package information. This method does not allow adding or removing quotes to the quote
     * package.
     *
     * @param string $quotePackageId ID of the quote package created using createQuote method.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function updateQuote($quotePackageId, array $payload, array $queryParams = [])
    {
        $requestUri = "/quotes/{$quotePackageId}/";

        return $this->call('PATCH', $requestUri, 200, $payload, $queryParams);
    }

    /**
     * Add quotes to quote package.
     *
     * This method can be used to add one or more quotes to existed quote package.
     *
     * @param string $quotePackageId ID of the quote package created using createQuote method.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function addQuotes($quotePackageId, array $payload, array $queryParams = [])
    {
        $requestUri = "/quotes/{$quotePackageId}/add/";

        return $this->call('POST', $requestUri, 201, $payload, $queryParams);
    }

    /**
     * Delete quotes from quote package.
     *
     * This method can be used to delete one or more quotes int the existed quote package.
     *
     * @param string $quotePackageId ID of the quote package created using createQuote method.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function deleteQuotes($quotePackageId, array $payload, array $queryParams = [])
    {
        $requestUri = "/quotes/{$quotePackageId}/delete/";

        return $this->call('POST', $requestUri, 200, $payload, $queryParams);
    }

    /**
     * Insurance opt-out.
     *
     * Used to notify XCover API about main product conversion in case if the customer choose to opt-out of the
     * insurance offering.
     *
     * @param string $quotePackageId ID of the quote package created using createQuote method.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function optOut($quotePackageId, array $payload, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/opt_out";

        return $this->call('POST', $requestUri, 204, $payload, $queryParams);
    }

    /**
     * Create booking.
     *
     * Used to convert specific quote to booking.
     *
     * @param string $quotePackageId ID of the quote package created using createQuote method.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function createBooking($quotePackageId, array $payload, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/";

        return $this->call('POST', $requestUri, 200, $payload, $queryParams);
    }

    /**
     * Instant booking.
     *
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function instantBooking(array $payload = null, array $queryParams = [])
    {
        $requestUri = '/instant_booking/';

        return $this->call('POST', $requestUri, 200, $payload, $queryParams);
    }

    /**
     * Get booking.
     *
     * Get a specific booking by the provided quote package id for the given partner
     *
     * @param string $quotePackageId ID of the quote package.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function getBooking($quotePackageId, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/";

        return $this->call('GET', $requestUri, 200, null, $queryParams);
    }

    /**
     * List bookings.
     *
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function listBookings(array $queryParams = [])
    {
        $requestUri = '/bookings/';

        return $this->call('GET', $requestUri, 200, null, $queryParams);
    }

    /**
     * Update booking.
     *
     * @param string $quotePackageId ID of the quote package.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function updateBooking($quotePackageId, array $payload, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/";

        return $this->call('PATCH', $requestUri, 200, $payload, $queryParams);
    }

    /**
     * Cancel booking.
     *
     * @param string $quotePackageId ID of the quote package.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function cancelBooking($quotePackageId, array $payload, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/cancel";

        return $this->call('POST', $requestUri, 200, $payload, $queryParams);
    }

    /**
     * Confirm booking.
     *
     * @param string $quotePackageId ID of the quote package.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function confirmBooking($quotePackageId, array $payload = null, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/confirm";

        return $this->call('PUT', $requestUri, 201, $payload, $queryParams);
    }

    /**
     * Quote for update.
     *
     * @param string $quotePackageId ID of the quote package.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function quoteForUpdate($quotePackageId, array $payload, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/quote_for_update";

        return $this->call('PATCH', $requestUri, 200, $payload, $queryParams);
    }

    /**
     * Confirm booking update.
     *
     * @param string $quotePackageId ID of the quote package.
     * @param string $updateId Update ID returned in quoteForUpdate call.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function confirmUpdate($quotePackageId, $updateId, array $payload = null, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/confirm_update/{$updateId}/";

        return $this->call('POST', $requestUri, 201, $payload, $queryParams);
    }

    /**
     * Confirm cancellation.
     *
     * @param string $quotePackageId ID of the quote package.
     * @param string $cancellationId Cancellation ID returned in cancelBooking call.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function confirmCancellation($quotePackageId, $cancellationId, array $payload = null, array $queryParams = [])
    {
        $requestUri = "/bookings/{$quotePackageId}/confirm_cancellation/{$cancellationId}/";

        return $this->call('POST', $requestUri, 200, $payload, $queryParams);
    }

    /**
     * Renewal confirmation.
     *
     * @param string $renewalId Renewal ID sent in the renewal webhook.
     * @param array $payload Request payload.
     * @param array $queryParams Optional query parameters.
     *
     * @return ResponseInterface
     *
     * @throws XCoverException
     */
    public function confirmRenewal($renewalId, array $payload = null, array $queryParams = [])
    {
        $requestUri = "/renewals/{$renewalId}/confirmation";

        return $this->call('PATCH', $requestUri, 202, $payload, $queryParams);
    }

    /**
     * Creates default Guzzle client
     *
     * @return Client
     */
    protected function createDefaultClient()
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push(
            new AuthMiddleware([
                'apiKey' => $this->config->apiKey(),
                'apiSecret' => $this->config->apiSecret(),
            ]),
            'auth'
        );
        $handlerStack->push(new JsonResponseMiddleware, 'json_response');

        return new Client([
            'http_errors' => false,
            'handler' => $handlerStack,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->config->apiKey(),
            ]
        ]);
    }

    /**
     * Returns the endpoint string using the given format
     *
     * @param string $path Relative path
     *
     * @return string
     */
    protected function makeUrl($path)
    {
        $partner = $this->config->partner();
        $basePath = rtrim($this->config->getBasePath(), '/');
        $path = ltrim($path, '/');

        return "$basePath/partners/$partner/$path";
    }
}
