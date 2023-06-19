<?php

namespace XCoverClient;

use XCoverClient\Exceptions\XCoverException;

/**
 * Class Config
 *
 * @package XCover
 */
class Config
{
    /**
     * @var mixed
     */
    protected $baseUrl;

    /**
     * @var mixed
     */
    protected $apiPrefix;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiSecret;

    /**
     * @var string
     */
    private $partner;

    /**
     * Config constructor.
     *
     * @param array $options
     * @throws XCoverException
     */
    public function __construct($options = [])
    {
        foreach (['baseUrl', 'apiPrefix', 'apiKey', 'apiSecret', 'partner'] as $key) {
            if (isset($options[$key])) {
                $this->$key = $options[$key];
            }
        }

        $this->validateCredentials();
    }

    /**
     * Getter method for base uri
     *
     * @return mixed
     */
    public function baseUri()
    {
        return $this->baseUrl;
    }

    /**
     * Getter method for api prefix
     *
     * @return mixed
     */
    public function apiPrefix()
    {
        return $this->apiPrefix;
    }

    /**
     * Getter method for api key
     *
     * @return mixed
     */
    public function apiKey()
    {
        return $this->apiKey;
    }

    /**
     * Getter method for api secret
     *
     * @return mixed
     */
    public function apiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * Getter method for partner
     *
     * @return mixed
     */
    public function partner()
    {
        return $this->partner;
    }

    /**
     * @throws XCoverException
     *
     * @return void
     */
    public function validateCredentials()
    {
        if (!$this->baseUrl || !$this->apiKey || !$this->apiSecret || !$this->partner) {
            throw new XCoverException('baseUrl, apiKey, apiSecret and partner are mandatory configuration options');
        }
    }

    /**
     * Returns the base path string based on the configured base url and prefix for the environment
     *
     * @return string
     */
    public function getBasePath()
    {
        $basePath = rtrim($this->baseUri(), '/');
        if (null === $this->apiPrefix()) {
            return $basePath;
        }

        return $basePath . '/' . rtrim($this->apiPrefix(), '/');
    }
}
