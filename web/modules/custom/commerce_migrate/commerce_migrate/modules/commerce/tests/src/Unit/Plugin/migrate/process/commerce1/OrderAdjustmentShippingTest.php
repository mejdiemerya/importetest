<?php

namespace Drupal\Tests\commerce_migrate_commerce\Unit\Plugin\migrate\process\commerce1;

use Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\OrderAdjustmentShipping;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the Order Adjustment Shipping plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\OrderAdjustmentShipping
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class OrderAdjustmentShippingTest extends MigrateProcessTestCase {

  /**
   * Tests valid input.
   *
   * @dataProvider providerTestOrderAdjustmentShipping
   */
  public function testOrderAdjustmentShipping($value = NULL, $expected = NULL) {
    $configuration = [];
    $this->plugin = new OrderAdjustmentShipping($configuration, 'map', []);
    $value = $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testOrderAdjustmentShipping().
   */
  public function providerTestOrderAdjustmentShipping() {
    return [
      'string' => [
        'invalid',
        [],
      ],
      'shipping line item' => [
        [
          'line_item_id' => '11',
          'order_id' => '1',
          'type' => 'shipping',
          'line_item_label' => 'Express shipping: 1 business day',
          'quantity' => '1.00',
          'created' => '1508452598',
          'changed' => '1508452598',
          'data' => [],
          'commerce_total' =>
            [
              [
                'amount' => '1500',
                'currency_code' => 'USD',
                'data' => [],
                'fraction_digits' => 2,
              ],
            ],
        ],
        [
          'type' => 'shipping',
          'label' => 'Express shipping: 1 business day',
          'amount' => '15',
          'currency_code' => 'USD',
          'sourceId' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Tests that exceptions are thrown as needed.
   *
   * @dataProvider providerTestInvalidValue
   */
  public function testInvalidValue($value = NULL, $message = NULL) {
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage($message);
    $this->plugin = new OrderAdjustmentShipping([], 'test', []);
    $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
  }

  /**
   * Data provider for testInvalidValue().
   */
  public function providerTestInvalidValue() {
    return [
      'commerce total missing' => [
        [
          'line_item_id' => '11',
          'order_id' => '1',
          'type' => 'shipping',
          'line_item_label' => 'Express shipping: 1 business day',
          'quantity' => '1.00',
          'created' => '1508452598',
          'changed' => '1508452598',
          'data' => [],
        ],
        "Adjustment does not have a total for destination 'foo'",
      ],
      'commerce total has no amount' => [
        [
          'line_item_id' => '11',
          'order_id' => '1',
          'type' => 'shipping',
          'line_item_label' => 'Express shipping: 1 business day',
          'quantity' => '1.00',
          'created' => '1508452598',
          'changed' => '1508452598',
          'data' => [],
          'commerce_total' =>
            [
              [
                'currency_code' => 'USD',
                'data' => [],
                'fraction_digits' => 2,
              ],
            ],
        ],
        "Adjustment total amount does not exist for destination 'foo'",
      ],
      'commerce total no currency code' => [
        [
          'line_item_id' => '11',
          'order_id' => '1',
          'type' => 'shipping',
          'line_item_label' => 'Express shipping: 1 business day',
          'quantity' => '1.00',
          'created' => '1508452598',
          'changed' => '1508452598',
          'data' => [],
          'commerce_total' =>
            [
              0 =>
                [
                  'amount' => '1500',
                  'data' => [],
                  'fraction_digits' => 2,
                ],
            ],
        ],
        "Adjustment currency code does not exist for destination 'foo'",
      ],
    ];
  }

}
