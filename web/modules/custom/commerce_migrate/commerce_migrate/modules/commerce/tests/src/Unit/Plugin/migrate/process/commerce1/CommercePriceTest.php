<?php

namespace Drupal\Tests\commerce_migrate_commerce\Unit\Plugin\migrate\process\commerce1;

use Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\CommercePrice;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the Commerce Price plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\CommercePrice
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class CommercePriceTest extends MigrateProcessTestCase {

  /**
   * Tests Commerce Price plugin.
   *
   * @dataProvider providerTestCommercePrice
   */
  public function testCommercePrice($value = NULL, $expected = NULL) {
    $configuration = [];
    $this->plugin = new CommercePrice($configuration, 'map', []);
    $value = $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'destinationproperty');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testSubstr().
   */
  public function providerTestCommercePrice() {
    // Test the input amount > 0 with different fraction digits.
    $tests[0]['value'] = [
      'amount' => '234',
      'currency_code' => 'NZD',
      'fraction_digits' => 0,
    ];
    $tests[0]['expected'] = [
      'number' => '234',
      'currency_code' => 'NZD',
    ];
    $tests[1]['value'] = $tests[0]['value'];
    $tests[1]['value']['fraction_digits'] = 1;
    $tests[1]['expected'] = [
      'number' => '23.4',
      'currency_code' => 'NZD',
    ];
    // Tests with fractional input.
    $tests[2]['value'] = [
      'amount' => '234.56',
      'currency_code' => 'NZD',
      'fraction_digits' => 0,
    ];
    $tests[2]['expected'] = [
      'number' => '234.56',
      'currency_code' => 'NZD',
    ];
    $tests[3]['value'] = [
      'amount' => '234.56',
      'currency_code' => 'NZD',
      'fraction_digits' => 3,
    ];
    $tests[3]['expected'] = [
      'number' => '0.23456',
      'currency_code' => 'NZD',
    ];
    // No faction digit.
    $tests[4]['value'] = [
      'amount' => '234.56',
      'currency_code' => 'NZD',
    ];
    $tests[4]['expected'] = [
      'number' => '2.3456',
      'currency_code' => 'NZD',
    ];
    return $tests;
  }

  /**
   * Tests that exception is thrown when input is not an array.
   *
   * @dataProvider providerTestNotArray
   */
  public function testNotArray($value = NULL) {
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage("CommercePrice input is not an array for destination 'new_value'");
    $this->plugin = new CommercePrice([], 'test_format_date', []);
    $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'new_value');
  }

  /**
   * Data provider for testSubstr().
   */
  public function providerTestNotArray() {
    // Test input not an array.
    $tests[0]['value'] = NULL;
    $tests[1]['value'] = 'string';
    $tests[2]['value'] = 1;
    return $tests;
  }

  /**
   * Tests that exception is thrown when input is not valid.
   *
   * @dataProvider providerTestInvalidValue
   */
  public function testInvalidValue($value = NULL) {
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage("CommercePrice input array is invalid for destination 'new_value'");
    $this->plugin = new CommercePrice([], 'test_format_date', []);
    $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'new_value');
  }

  /**
   * Data provider for testSubstr().
   */
  public function providerTestInvalidValue() {
    return [
      'Missing fraction_digits and currency_code keys' => [
        [
          'amount' => '234',
        ]
      ],
      'Missing currency_code key' => [
        [
          'amount' => '234',
          'fraction_digits' => 0,
        ],
      ],
      'Missing amount key' => [
        [
          'currency_code' => 'NZD',
          'fraction_digits' => 0,
        ],
      ],
      'Invalid fraction_digits' => [
        [
          'amount' => '234',
          'currency_code' => 'NZD',
          'fraction_digits' => -1,
        ],
      ],
      'Empty amount' => [
        [
          'amount' => NULL,
        ],
      ]
    ];
  }

}
