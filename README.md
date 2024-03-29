<p align="center">
    <img src="xcover-logo.svg" />
</p>

<p align="center">
    <img src="https://github.com/CoverGenius/xcover-php/workflows/CI/badge.svg" />
    <img src="https://github.com/CoverGenius/xcover-php/workflows/Lint/badge.svg" />
    <img src="https://github.com/CoverGenius/xcover-php/workflows/Composer/badge.svg" />
    <img src="https://github.com/CoverGenius/xcover-php/workflows/Semgrep/badge.svg" />
    <img src="https://github.com/CoverGenius/xcover-php/workflows/Static%20Analysis/badge.svg" />
</p>

<p align="center">
    <a href="https://packagist.org/packages/CoverGenius/xcover-php"><img alt="Latest Stable Version" src="http://poser.pugx.org/CoverGenius/xcover-php/v?style=plastic" /></a>
    <a href="https://packagist.org/packages/CoverGenius/xcover-php"><img alt="PHP Version Require" src="http://poser.pugx.org/CoverGenius/xcover-php/require/php?style=plastic" /></a>
    <a href="https://packagist.org/packages/CoverGenius/xcover-php"><img alt="License" src="http://poser.pugx.org/CoverGenius/xcover-php/license?style=plastic" /></a>
</p>


# XCover SDK for PHP

XCover SDK simplifies [XCover API](https://www.covergenius.com/api/docs/xcover/) integration in PHP applications.     
The library is based on Guzzle 7 HTTP client and offers the following features:

  * AuthMiddleware performs authentication
  * JsonResponseMiddleware provides convenient `json` method on the Guzzle responses 
  * HTTP abstraction in XCover class
  * Automatic response status code validation and custom exception classes

## Installation

XCover SDK is available on [Packagist](https://packagist.org/packages/covergenius/xcover-php) and the recommended way of installing it is via [Composer](https://getcomposer.org/).

V1 releases are for PHP 7.2 or higher (but less than 8)

V2 releases are for PHP 7.4 as well as 8.0 and higher

```bash
composer require covergenius/xcover-php
```

## Usage

### Basic usage

```php

use XCoverClient\Config;
use XCoverClient\XCover;

// Instantiate client
$client = new XCover(new Config([
  'baseUrl' => env('BASE_URL'),
  'apiPrefix' => env('API_PREFIX'),
  'apiKey' => env('API_KEY'),
  'apiSecret' => env('API_SECRET'),
  'partner' => env('PARTNER_CODE'),
]));

// Quote request
$quoteResponse = $client->createQuote(
    [
        'request' => [
            [
                'policy_type' => 'event_ticket_protection', 
                'policy_type_version' => 1, 
                'policy_start_date' => '2019-12-01T17:59:00.831+00:00', 
                'event_datetime' => '2019-12-25T21:00:00+00:00', 
                'event_name' => 'Ariana Grande', 
                'event_location' => 'The O2', 
                'number_of_tickets' => 2, 
                'total_ticket_price' => 100, 
                'resale_ticket' => false, 
                'event_country' => 'GB' 
            ] 
        ], 
        'currency' => 'GBP',
        'customer_country' => 'GB',
        'customer_region' => 'London',
        'customer_language' => 'en' 
    ]
);
$quotePackage = $quoteResponse->json();


// Quote package array will contain all information required to display the insurance offering
echo $quotePackage['id']; // 'JWFFM-M3W3Y-INS'
echo $quotePackage['total_price']; // 5.00
echo $quotePackage['quotes'][0]['price']; // 5.00
echo $quotePackage['quotes'][0]['tax']['total_tax']; // 1.00
echo $quotePackage['quotes'][0]['tax']['total_tax_formatted']; // '£ 1.00'
echo $quotePackage['quotes'][0]['content']['title']; // 'Ticket Protection'
echo $quotePackage['quotes'][0]['content']['description']; // 'Covers the purchase cost of tickets (up to a maximum of £500) if you are unable to attend a booked event as result of an unexpected circumstance.'
echo $quotePackage['quotes'][0]['pds_url']; // 'https://xcover.com/en/pds/JWFFM-M3W3Y-INS?policy_type=event_ticket_protection_v1'


// To report insurance booking
echo $quotePackage['quotes'][0]['id']; // '40e9859d-9a2c-47fb-a0a1-5d121fc68fdd'
$bookingResponse = $client->createBooking($quotePackage['id'],
    [
        'quotes' => [
            [
                 'id' => '40e9859d-9a2c-47fb-a0a1-5d121fc68fdd',
            ]
        ],
        'policyholder' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@gmail.com',
            'age' => 30,
            'country' => 'GB'
        ]
]
);

// Booking has the same id as quote package and a similar structure
$booking = $bookingResponse->json();

echo $booking['id']; // 'JWFFM-M3W3Y-INS'
echo $booking['status']; // 'CONFIRMED'
echo $booking['total_price']; // 5.00
```

Please refer to `tests/XCoverTest.php` file for more examples.

XCover class provides methods for all operations listed in [XCover API Documentation](https://www.covergenius.com/api/docs/xcover/).

### Customising Guzzle client

To customise Guzzle configuration you can pass custom client instance in XCover constructor.

Guzzle 6 client instance is immutable so you need to prepare it beforehand and add all mandatory config options, e.g:
  
```php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use XCoverClient\Config;
use XCoverClient\Middleware\AuthMiddleware;
use XCoverClient\Middleware\JsonResponseMiddleware;
use XCoverClient\XCover;


// Add XCover mandatory middlewares
$handlerStack = HandlerStack::create();
$handlerStack->push(
  new AuthMiddleware([
      'apiKey' => $this->config->apiKey(),
      'apiSecret' => $this->config->apiSecret(),
  ]),
  'auth'
);
$handlerStack->push(new JsonResponseMiddleware, 'json_response');

// You can add your custom middlewares here      

// You can add your custom options to the Guzzle's Client constructor below
$client =  new Client([
  'handler' => $handlerStack,
  'headers' => [
      'Content-Type' => 'application/json',
      'X-Api-Key' => $this->config->apiKey(),
  ],
]);

// Now pass it to XCover constructor as second argument
$client = new XCover(new Config([
  'baseUrl' => env('BASE_URL'),
  'apiPrefix' => env('API_PREFIX'),
  'apiKey' => env('API_KEY'),
  'apiSecret' => env('API_SECRET'),
  'partner' => env('PARTNER_CODE'),
]), $client);
```

It is also possible to extend XCover Class and override `createDefaultClient` function.

### TODO

There are a few features which we will add in the future releases:
    
  * Async calls using Guzzle's promises
  * CircuitBreaker middleware
  * BYO HTTP Client


## Testing

PHPUnit with PHP-VCR are used to run tests.

Tests are run on every push and pull request to the repository.

Run the below command to run tests

```bash
composer test
```

## Code Style

[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) is used for coding standard. We currently follow PSR-1 and PSR-2 as a standard.

Lint checks are run on every push and pull request.

Run the below command to check for any code style issues.

```bash
composer lint
```

You can view the php-cs rules at [phpcs.xml](phpcs.xml)

## Static Analysis

[PHPStan](https://github.com/phpstan/phpstan) is used for static analysis of code. PHPStan is a PHP Static Analysis Tool. PHPStan focuses on finding errors in your code without actually running it.
It catches whole classes of bugs even before you write tests for the code.

Static Analysis is run on every push and pull request

run the command below to check the code with PHPStan

```bash
composer analyse
```

## Changelog

You can view the changelog at [CHANGELOG.md](CHANGELOG.md)

## Security

You can iew the security policy at [SECURITY.md](SECURITY.md)

## Licence

You can view license information at [LICENSE](LICENSE)
