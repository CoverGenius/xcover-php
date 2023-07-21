<?php

namespace XCoverClient\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseTestCase
 * @package XCover\Tests
 */
abstract class BaseTestCase extends TestCase
{
    /**
     * Get the json payload from a .json file and return as php array
     *
     * @param string $file
     * @param array $context
     *
     * @return mixed
     */
    public function getPayloadFromFile(string $file, $context = [])
    {
        $payload = file_get_contents(dirname(__FILE__) . '/__fixtures__/' . $file);

        if (!empty($context)) {
            $payload = str_replace(array_map(function ($key) {
                return sprintf("{{%s}}", $key);
            }, array_keys($context)), array_values($context), $payload);
        }

        return json_decode($payload, true);
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param array $structure
     * @param array|null $json
     *
     * @return $this
     */
    public function assertJsonStructure(array $structure, $json = null)
    {
        if (is_null($json)) {
            Assert::fail('Invalid response data provided.');
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                Assert::assertIsArray($json);

                foreach ($json as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                Assert::assertArrayHasKey($key, $json);

                $this->assertJsonStructure($structure[$key], $json[$key]);
            } else {
                Assert::assertArrayHasKey($value, $json);
            }
        }

        return $this;
    }
}
