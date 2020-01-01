<?php

namespace XCoverClient\Middleware;

use Psr\Http\Message\RequestInterface;

/**
 * Class AuthMiddleware
 *
 * @package XCover\Middleware
 */
class AuthMiddleware
{
    const SIGNATURE_ALGORITHM = 'sha512';

    private $apiKey;
    private $apiSecret;
    protected $configs = [];

    /**
     * AuthMiddleware constructor
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;

        if (isset($configs['apiKey'])) {
            $this->apiKey = $configs['apiKey'];
            unset($configs['apiKey']);
        }

        if (isset($configs['apiSecret'])) {
            $this->apiSecret = $configs['apiSecret'];
            unset($configs['apiSecret']);
        }
    }

    /**
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        $date = gmdate('D, d M Y H:i:s T');
        $headerValue = $this->withAuthTokenHeaders(array_merge($this->configs, ['date' => $date]));

        return function (RequestInterface $request, array $options = []) use ($handler, $headerValue, $date) {
            $request = $request
                ->withHeader('Authorization', $headerValue)
                ->withHeader('Date', $date);

            return $handler($request, $options);
        };
    }

    /**
     * Returns the Authorization header string
     *
     * @param array $options
     *
     * @return string
     */
    private function withAuthTokenHeaders(array $options)
    {
        $signatureContentString = $this->makeSignatureContentString($options);

        $signatureString = urlencode($this->makeSignatureString($signatureContentString));

        return sprintf($this->signatureTemplate(), $this->apiKey, $signatureString);
    }

    /**
     * Returns a signature template
     *
     * @return string
     */
    private function signatureTemplate()
    {
        return 'Signature keyId="%s",algorithm="hmac-' . static::SIGNATURE_ALGORITHM . '",signature="%s"';
    }

    /**
     * Make the signature string
     *
     * @param $signatureContentString
     *
     * @return string
     */
    private function makeSignatureString($signatureContentString)
    {
        return base64_encode(
            hash_hmac(
                static::SIGNATURE_ALGORITHM,
                $signatureContentString,
                utf8_encode($this->apiSecret),
                true
            )
        );
    }

    /**
     * Make the signature content string from given parts
     *
     * @param array $parts
     *
     * @return string
     */
    private function makeSignatureContentString(array $parts)
    {
        return implode("\n", [
            'date: ' . $parts['date'],
        ]);
    }
}