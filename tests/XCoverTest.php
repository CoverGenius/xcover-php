<?php

namespace Tests;

use VCR\VCR;
use XCoverClient\Config;
use XCoverClient\Response;
use XCoverClient\Tests\BaseTestCase;
use XCoverClient\XCover;

class XCoverTest extends BaseTestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function can_make_a_get_request()
    {
        VCR::insertCassette('can_make_a_get_request.json');
        $client = $this->getClient();
        $response = $client->call('GET', '/', 200, []);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     *
     * @return void
     */
    public function can_make_a_post_request()
    {
        VCR::insertCassette('can_make_a_post_request.json');

        $client = $this->getClient();
        $response = $client->call('POST', '/', 201, []);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_booking_success()
    {
        VCR::insertCassette('create_booking_success.json');
        $responseBody = $this->createBooking();
        $this->assertArrayHasKey('quotes', $responseBody);
        $this->assertArrayHasKey('policyholder', $responseBody);
        $this->assertJsonStructure([
            'id',
            'status',
            'quotes' => [
                [
                    'id',
                    'policy_start_date',
                    'policy_end_date',
                    'status',
                    'policy' => [
                        'policy_type',
                        'policy_name',
                        'policy_code',
                        'policy_version',
                        'underwriter'
                    ],
                    'insured' => [
                        [
                            'first_name',
                            'last_name',
                            'email'
                        ]
                    ]
                ]
            ],
            'currency',
            'policyholder' => [
                'first_name',
                'last_name',
                'email',
                'age',
                'country'
            ]
        ], $responseBody);
    }

    /**
     * @test
     *
     * @return void
     */
    public function get_booking_success()
    {
        VCR::insertCassette('get_booking_success.json');

        $booking = $this->createBooking();

        $client = $this->getClient();
        $response = $client->getBooking($booking['id'], [
            'language' => 'en'
        ]);

        $responseBody = $response->json();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey('status', $responseBody);
        $this->assertArrayHasKey('quotes', $responseBody);
        $this->assertArrayHasKey('pds_url', $responseBody);
        $this->assertArrayHasKey('coi', $responseBody);
        $this->assertArrayHasKey('policyholder', $responseBody);
        $this->assertArrayHasKey('total_tax', $responseBody);
        $this->assertArrayHasKey('total_premium', $responseBody);
    }

    /**
     * @test
     *
     * @return void
     */
    public function get_booking_with_query_params_success()
    {
        VCR::insertCassette('get_booking_with_query_params_success.json');

        $booking = $this->createBooking();

        $client = $this->getClient();

        $response = $client->getBooking(
            $booking['id'],
            [
                'language' => 'en-us',
                'date' => (new \DateTime())->format('Y-m-d\TH:i:s.u\Z')
            ]
        );

        $responseBody = $response->json();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey('status', $responseBody);
        $this->assertArrayHasKey('quotes', $responseBody);
        $this->assertArrayHasKey('pds_url', $responseBody);
        $this->assertArrayHasKey('coi', $responseBody);
        $this->assertArrayHasKey('policyholder', $responseBody);
        $this->assertArrayHasKey('total_tax', $responseBody);
        $this->assertArrayHasKey('total_premium', $responseBody);
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_quote_422()
    {
        VCR::insertCassette('create_quote_422.json');

        $this->expectException(\XCoverClient\Exceptions\ResponseException::class);
        $this->expectExceptionMessage("validation_error");
        $client = $this->getClient();
        $client->createQuote(
            [
                'malformed_payload' => true,
            ]
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function create_quote_success()
    {
        VCR::insertCassette('create_quote_success.json');

        $quote = $this->createQuote();
        $this->assertMatchesRegularExpression('/\-INS$/', $quote['id']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function get_quote_success()
    {
        VCR::insertCassette('get_quote_success.json');

        $quote = $this->createQuote();

        $client = $this->getClient();
        $response = $client->getQuote($quote['id']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function opt_out_422()
    {
        VCR::insertCassette('opt_out_422.json');

        $this->expectException(\XCoverClient\Exceptions\ResponseException::class);
        $this->expectExceptionMessage("validation_error");
        $quote = $this->createQuote();

        $client = $this->getClient();
        $client->optOut(
            $quote['id'],
            [
                'malformed_payload' => true,
            ]
        );
    }

    /**
     * @test
     *
     * @return void
     */
    public function opt_out_success()
    {
        VCR::insertCassette('opt_out_success.json');

        $quote = $this->createQuote();

        $client = $this->getClient();
        $response = $client->optOut(
            $quote['id'],
            [
                'partner_metadata' => [
                    'extras_purchased' => true,
                ],
            ]
        );
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function update_quote_success()
    {
        VCR::insertCassette('update_quote_success.json');

        $quote = $this->createQuote();
        $client = $this->getClient();
        $response = $client->updateQuote($quote['id'], [
            'currency' => 'AUD',
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function update_quote_422()
    {
        VCR::insertCassette('update_quote_422.json');

        $this->expectException(\XCoverClient\Exceptions\ResponseException::class);
        $this->expectExceptionMessage("validation_error");
        $quote = $this->createQuote();
        $client = $this->getClient();
        $client->updateQuote($quote['id'], [
            'malformed' => 'payload',
        ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function add_quotes_422()
    {
        VCR::insertCassette('add_quotes_422.json');

        $this->expectException(\XCoverClient\Exceptions\ResponseException::class);
        $this->expectExceptionMessage("validation_error");
        $quote = $this->createQuote();
        $client = $this->getClient();
        $client->addQuotes($quote['id'], [
            'malformed' => 'payload',
        ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function add_quote_success()
    {
        VCR::insertCassette('add_quotes_success.json');

        $quote = $this->createQuote();
        $client = $this->getClient();
        $response = $client->addQuotes($quote['id'], $this->getPayloadFromFile(
            'sample-add-quote.json',
            [
                'policy_start_date' => $this->getNow(),
            ]
        ));

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertCount(2, $response->json()['quotes']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function delete_quotes_422()
    {
        VCR::insertCassette('delete_quotes_422.json');

        $this->expectException(\XCoverClient\Exceptions\ResponseException::class);
        $this->expectExceptionMessage("validation_error");
        $client = $this->getClient();
        $quote = $client->createQuote(
            $this->getPayloadFromFile(
                'sample-create-quote.json',
                [
                    'policy_start_date' => $this->getNow(),
                ]
            )
        )->json();

        $client->deleteQuotes($quote['id'], [
            'malformed' => 'payload',
        ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function delete_quotes_success()
    {
        VCR::insertCassette('delete_quotes_success.json');

        $client = $this->getClient();
        $quote = $client->createQuote(
            $this->getPayloadFromFile(
                'sample-create-multiple-quote.json',
                [
                    'policy_start_date' => $this->getNow(),
                ]
            )
        )->json();

        $response = $client->deleteQuotes($quote['id'], [
            'quotes' => [
                [
                    'id' => $quote['quotes']['0']['id'],
                ]
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(1, $response->json()['quotes']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function list_bookings()
    {
        VCR::insertCassette('list_bookings.json');

        $client = $this->getClient();
        $bookings = $client->listBookings(['limit' => 10])->json();
        $this->assertCount(10, $bookings['results']);
    }

    /**
     * @test
     *
     * @return void
     */
    public function update_booking_422()
    {
        VCR::insertCassette('update_booking_422.json');

        $this->expectException(\XCoverClient\Exceptions\ResponseException::class);
        $this->expectExceptionMessage("validation_error");
        $booking = $this->createBooking();
        $client = $this->getClient();
        $client->updateBooking($booking['id'], [
            'malformed' => 'payload',
        ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function update_booking_success()
    {
        VCR::insertCassette('update_booking_success.json');

        $booking = $this->createBooking();
        $client = $this->getClient();
        $response = $client->updateBooking($booking['id'], [
            'quotes' => [[
                'id' => $booking['quotes'][0]['id'],
                'update_fields' => ['number_of_tickets' => 3],
            ]],
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function cancel_booking_422()
    {
        VCR::insertCassette('cancel_booking_422.json');

        $this->expectException(\XCoverClient\Exceptions\ResponseException::class);
        $this->expectExceptionMessage("validation_error");
        $booking = $this->createBooking();
        $client = $this->getClient();
        $client->cancelBooking($booking['id'], [
            'malformed' => 'payload'
        ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function cancel_booking_success()
    {
        VCR::insertCassette('cancel_booking_success.json');

        $booking = $this->createBooking();
        $client = $this->getClient();
        $response = $client->cancelBooking($booking['id'], [
            'quotes' => [[
                'id' => $booking['quotes'][0]['id'],
                'reason_for_cancellation' => 'No longer wanted.',
            ]],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function confirm_booking_success()
    {
        VCR::insertCassette('confirm_booking_success.json');

        $client = $this->getClient();

        $quote = $this->createQuote();
        $bookingParams = $this->getPayloadFromFile('sample-create-booking-with-confirmation.json', [
            'quote_id' => $quote['quotes']['0']['id'],
        ]);
        $booking = $client->createBooking($quote['id'], $bookingParams)->json();
        $response = $client->confirmBooking($booking['id']);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function update_with_confirmation_success()
    {
        VCR::insertCassette('update_with_confirmation_success.json');

        $client = $this->getClient();
        $booking = $this->createBooking();

        $update = $client->quoteForUpdate($booking['id'], [
            'quotes' => [[
                'id' => $booking['quotes'][0]['id'],
                'update_fields' => ['number_of_tickets' => 3],
            ]],
        ]);
        $this->assertEquals(200, $update->getStatusCode());

        $updateId = $update->json()['update_id'];
        $confirmation = $client->confirmUpdate($booking['id'], $updateId);
        $this->assertEquals(201, $confirmation->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function cancellation_with_confirmation_success()
    {
        VCR::insertCassette('cancellation_with_confirmation_success.json');

        $client = $this->getClient();
        $booking = $this->createBooking();

        $update = $client->cancelBooking($booking['id'], [
            'preview' => true,
        ]);
        $this->assertEquals(200, $update->getStatusCode());

        $cancellationId = $update->json()['cancellation_id'];
        $confirmation = $client->confirmCancellation($booking['id'], $cancellationId);
        $this->assertEquals(200, $confirmation->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function instant_booking_success()
    {
        VCR::insertCassette('instant_booking_success.json');

        $client = $this->getClient();
        $bookingParams = $this->getPayloadFromFile('sample-instant-booking.json', [
            'policy_start_date' => $this->getNow(),
        ]);
        $response = $client->instantBooking($bookingParams);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @return void
     */
    public function renewal_confirmation_success()
    {
        VCR::insertCassette('renewal_confirmation_success.json');

        $client = $this->getClient();
        $response = $client->confirmRenewal('IvtoW-zEZqf-REN', [
            'paid_on' => '2019-12-20 02:42:12Z',
        ]);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * @return mixed
     * @throws \XCoverClient\Exceptions\XCoverException
     */
    protected function createQuote()
    {
        $client = $this->getClient();

        $response = $client->createQuote(
            $this->getPayloadFromFile(
                'sample-create-quote.json',
                [
                    'policy_start_date' => $this->getNow(),
                ]
            )
        );

        return $response->json();
    }

    /**
     * @return mixed
     * @throws \XCoverClient\Exceptions\XCoverException
     */
    protected function createBooking()
    {
        $quote = $this->createQuote();
        $client = $this->getClient();
        $bookingParams = $this->getPayloadFromFile('sample-create-booking.json', [
            'quote_id' => $quote['quotes']['0']['id'],
        ]);

        return $client->createBooking($quote['id'], $bookingParams)->json();
    }

    /**
     * @return string
     */
    protected function getNow()
    {
        $date = new \DateTime();
        $date->modify('+1 day');

        return $date->format(\DateTime::RFC3339_EXTENDED);
    }

    /**
     * @return XCover
     * @throws \XCoverClient\Exceptions\XCoverException
     */
    protected function getClient()
    {
        return new XCover(new Config([
            'baseUrl' => env('BASE_URL'),
            'apiPrefix' => env('API_PREFIX'),
            'apiKey' => env('API_KEY'),
            'apiSecret' => env('API_SECRET'),
            'partner' => env('PARTNER_CODE'),
        ]));
    }
}
