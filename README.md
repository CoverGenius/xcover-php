# XCover PHP SDK

The SDK to communicate with XCover

## Requirements

* Minimum PHP 5.6.*

## Getting Started

Some getting started setups.

## Reference

Some References to the architecture and class design.

## Example

- **Booking/Policy**
    - [Get booking](./docs/examples/get_booking.md)
    
# Retrieve Booking/Policy

by distributor code and booking reference via `getBooking` REST API.

This example covers getting the policy information for the given distributor and the given booking reference code.

You will require the API key and the API secret to be able to get the policy information.

## Example [Get booking](https://api.xcover.com/api/xcover-docs/#operation/Get%20booking)

```php
use XCoverClient\XCover;
new XCoverClient\Config;

$client = new XCover(new Config([
  'baseUrl' => env('BASE_URL'),
  'apiPrefix' => env('API_PREFIX'),
  'apiKey' => env('API_KEY'),
  'apiSecret' => env('API_SECRET'),
  'partner' => env('PARTNER_CODE'),
]));

$bookingResponse = $client->getBooking({distributor-code}, {booking-reference});

// response body
$booking = $bookingResponse->getBody();

// response code
$bookingResponse->getHttpResponseCode();

// response headers
$bookingResponse->getHeaders();

// booking status
$booking['status']

// quotes array
$booking['quotes']

// a typical booking response will look like this:
array:13 [
  "id" => "9NF7L-MUJPC-INS"
  "status" => "CONFIRMED"
  "quotes" => array:1 [
    0 => array:16 [
      "id" => "54d208ef-e7ed-4a29-b104-63a23871b435"
      "policy_start_date" => "2018-12-02T13:00:00Z"
      "policy_end_date" => null
      "status" => "CONFIRMED"
      "price" => 1.57
      "price_formatted" => "A$ 1.57"
      "policy" => array:6 [
        "policy_type" => "parcel_insurance"
        "policy_name" => "Test parcel insurance"
        "policy_code" => "ABNM2323"
        "policy_version" => "5c89d4a5-624e-4d33-9971-6aed2f59b16a"
        "content" => array:4 [
          "section_header" => ""
          "description" => ""
          "inclusions" => []
          "exclusions" => []
        ]
        "underwriter" => array:1 [
          "disclaimer" => "this is a disclaimer"
        ]
      ]
      "insured" => array:1 [
        0 => array:3 [
          "first_name" => "Test 2"
          "last_name" => "Test 2"
          "email" => "girish+op@rentalcover.com"
        ]
      ]
      "tax" => array:5 [
        "total_tax" => 0.0
        "total_amount_without_tax" => 1.57
        "total_tax_formatted" => "A$ 0.00"
        "total_amount_without_tax_formatted" => "A$ 1.57"
        "taxes" => []
      ]
      "commission" => array:2 [
        "total_commission" => 0.0
        "total_commission_formatted" => "A$ 0.00"
      ]
      "created_at" => "2018-11-28T03:10:25.793765Z"
      "confirmed_at" => "2018-11-28T05:24:50.328153Z"
      "updated_at" => null
      "cancelled_at" => null
      "is_renewable" => false
      "pending_renewal" => null
    ]
  ]
  "currency" => "AUD"
  "total_price" => 1.57
  "total_price_formatted" => "A$ 1.57"
  "created_at" => "2018-11-28T03:10:25.758529Z"
  "updated_at" => "2018-11-28T05:24:50.339816Z"
  "pds_url" => "https://ap-southeast-2.staging.xcover.com/pds/9NF7L-MUJPC-INS"
  "coi" => array:2 [
    "url" => "https://ap-southeast-2.staging.xcover.com/coi/9NF7L-MUJPC-INS?security_token=0aNdI-y4JuH-8rDnn-0bjrN"
    "pdf" => "https://ap-southeast-2.staging.xcover.com/coi/9NF7L-MUJPC-INS.pdf?security_token=0aNdI-y4JuH-8rDnn-0bjrN"
  ]
  "policyholder" => array:5 [
    "first_name" => "Test 1"
    "last_name" => "Test 1"
    "email" => "maxim+postman@rentalcover.com"
    "age" => 30
    "country" => "AU"
  ]
  "total_tax" => 0.0
  "total_premium" => 1.57
]
```
