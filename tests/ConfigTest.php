<?php

namespace Tests;

use XCoverClient\Config;
use XCoverClient\Tests\BaseTestCase;

class ConfigTest extends BaseTestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function client_throws_error_if_base_url_is_empty()
    {
        $this->expectException(\XCoverClient\Exceptions\XCoverException::class);
        new Config([
            'baseUrl' => '',
            'apiPrefix' => env('API_PREFIX'),
            'apiKey' => env('API_KEY'),
            'apiSecret' => env('API_SECRET'),
            'partner' => env('PARTNER_CODE'),
        ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function client_throws_error_if_api_key_is_empty()
    {
        $this->expectException(\XCoverClient\Exceptions\XCoverException::class);
        new Config([
            'baseUrl' => env('BASE_URL'),
            'apiPrefix' => env('API_PREFIX'),
            'apiKey' => '',
            'apiSecret' => env('API_SECRET'),
            'partner' => env('PARTNER_CODE'),
        ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function client_throws_error_if_api_secret_is_empty()
    {
        $this->expectException(\XCoverClient\Exceptions\XCoverException::class);
        new Config([
            'baseUrl' => env('BASE_URL'),
            'apiPrefix' => env('API_PREFIX'),
            'apiKey' => env('API_KEY'),
            'apiSecret' => '',
            'partner' => env('PARTNER_CODE'),
        ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function client_throws_error_if_partner_is_empty()
    {
        $this->expectException(\XCoverClient\Exceptions\XCoverException::class);
        new Config([
            'baseUrl' => env('BASE_URL'),
            'apiPrefix' => env('API_PREFIX'),
            'apiKey' => env('API_KEY'),
            'apiSecret' => env('API_SECRET'),
            'partner' => '',
        ]);
    }
}
