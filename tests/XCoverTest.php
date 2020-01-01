<?php

namespace Tests;

use XCoverClient\Config;
use XCoverClient\Response;
use XCoverClient\Tests\BaseTestCase;
use XCoverClient\XCover;

class XCoverTest extends BaseTestCase
{
    /**
     * @test
     *
     * @vcr can_make_a_get_request.json
     */
    public function can_make_a_get_request()
    {
        $client = $this->getClient();
        $response = $client->call('GET', '/', 200, []);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     *
     * @vcr can_make_a_post_request.json
     */
    public function can_make_a_post_request()
    {
        $client = $this->getClient();
        $response = $client->call('POST', '/', 201, []);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     *
     * @vcr create_booking_success.json
     */
    public function create_booking_success()
    {
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
     * @vcr get_booking_success.json
     */
    public function get_booking_success()
    {
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
     * @vcr get_booking_with_query_params_success.json
     */
    public function get_booking_with_query_params_success()
    {
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
     * @expectedException \XCoverClient\Exceptions\ResponseException
     * @expectedExceptionMessage validation_error
     *
     * @vcr create_quote_422.json
     */
    public function create_quote_422()
    {
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
     * @vcr create_quote_success.json
     */
    public function create_quote_success()
    {
        $quote = $this->createQuote();
        $this->assertRegExp('/\-INS$/', $quote['id']);
    }

    /**
     * @test
     *
     * @vcr get_quote_success.json
     */
    public function get_quote_success()
    {
        $quote = $this->createQuote();

        $client = $this->getClient();
        $response = $client->getQuote($quote['id']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @expectedException \XCoverClient\Exceptions\ResponseException
     * @expectedExceptionMessage validation_error
     *
     * @vcr opt_out_422.json
     */
    public function opt_out_422()
    {
        $quote = $this->createQuote();

        $client = $this->getClient();
        $client->optOut($quote['id'],
            [
                'malformed_payload' => true,
            ]
        );
    }

    /**
     * @test
     *
     * @vcr opt_out_success.json
     */
    public function opt_out_success()
    {
        $quote = $this->createQuote();

        $client = $this->getClient();
        $response = $client->optOut($quote['id'],
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
     * @vcr update_quote_success.json
     */
    public function update_quote_success()
    {
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
     * @expectedException \XCoverClient\Exceptions\ResponseException
     * @expectedExceptionMessage validation_error
     *
     * @vcr update_quote_422.json
     */
    public function update_quote_422()
    {
        $quote = $this->createQuote();
        $client = $this->getClient();
        $client->updateQuote($quote['id'], [
            'malformed' => 'payload',
        ]);
    }

    /**
     * @test
     *
     * @expectedException \XCoverClient\Exceptions\ResponseException
     * @expectedExceptionMessage validation_error
     *
     * @vcr add_quotes_422.json
     */
    public function add_quotes_422()
    {
        $quote = $this->createQuote();
        $client = $this->getClient();
        $client->addQuotes($quote['id'], [
            'malformed' => 'payload',
        ]);
    }

    /**
     * @test
     *
     * @vcr add_quotes_success.json
     */
    public function add_quote_success()
    {
        $quote = $this->createQuote();
        $client = $this->getClient();
        $response = $client->addQuotes($quote['id'], $this->getPayloadFromFile('sample-add-quote.json',
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
     * @expectedException \XCoverClient\Exceptions\ResponseException
     * @expectedExceptionMessage validation_error
     *
     * @vcr delete_quotes_422.json
     */
    public function delete_quotes_422()
    {
        $client = $this->getClient();
        $quote = $client->createQuote(
            $this->getPayloadFromFile('sample-create-quote.json',
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
     * @vcr delete_quotes_success.json
     */
    public function delete_quotes_success()
    {
        $client = $this->getClient();
        $quote = $client->createQuote(
            $this->getPayloadFromFile('sample-create-multiple-quote.json',
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
     * @vcr list_bookings.json
     */
    public function list_bookings()
    {
        $client = $this->getClient();
        $bookings = $client->listBookings(['limit' => 10])->json();
        $this->assertCount(10, $bookings['results']);
    }

    /**
     * @test
     *
     * @expectedException \XCoverClient\Exceptions\ResponseException
     * @expectedExceptionMessage validation_error
     *
     * @vcr update_booking_422.json
     */
    public function update_booking_422()
    {
        $booking = $this->createBooking();
        $client = $this->getClient();
        $client->updateBooking($booking['id'], [
            'malformed' => 'payload',
        ]);
    }

    /**
     * @test
     *
     * @vcr update_booking_success.json
     */
    public function update_booking_success()
    {
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
     * @expectedException \XCoverClient\Exceptions\ResponseException
     * @expectedExceptionMessage validation_error
     *
     * @vcr cancel_booking_422.json
     */
    public function cancel_booking_422()
    {
        $booking = $this->createBooking();
        $client = $this->getClient();
        $client->cancelBooking($booking['id'], [
            'malformed' => 'payload'
        ]);
    }

    /**
     * @test
     *
     * @vcr cancel_booking_success.json
     */
    public function cancel_booking_success()
    {
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
     * @vcr confirm_booking_success.json
     */
    public function confirm_booking_success()
    {
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
     * @vcr update_with_confirmation_success.json
     */
    public function update_with_confirmation_success()
    {
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
     * @vcr cancellation_with_confirmation_success.json
     */
    public function cancellation_with_confirmation_success()
    {
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
     * @vcr instant_booking_success.json
     */
    public function instant_booking_success()
    {
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
     * @vcr renewal_confirmation_success.json
     */
    public function renewal_confirmation_success()
    {
        $client = $this->getClient();
        $response = $client->confirmRenewal('IvtoW-zEZqf-REN', [
            'paid_on' => '2019-12-20 02:42:12Z',
        ]);
        $this->assertEquals(202, $response->getStatusCode());
    }

    protected function createQuote()
    {
        $client = $this->getClient();

        $response = $client->createQuote(
            $this->getPayloadFromFile('sample-create-quote.json',
                [
                    'policy_start_date' => $this->getNow(),
                ]
            )
        );

        return $response->json();
    }

    protected function createBooking()
    {
        $quote = $this->createQuote();
        $client = $this->getClient();
        $bookingParams = $this->getPayloadFromFile('sample-create-booking.json', [
            'quote_id' => $quote['quotes']['0']['id'],
        ]);

        return $client->createBooking($quote['id'], $bookingParams)->json();
    }

    protected function getNow()
    {
        $date = new \DateTime();
        $date->modify('+1 day');

        return $date->format(\DateTime::RFC3339_EXTENDED);
    }

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
