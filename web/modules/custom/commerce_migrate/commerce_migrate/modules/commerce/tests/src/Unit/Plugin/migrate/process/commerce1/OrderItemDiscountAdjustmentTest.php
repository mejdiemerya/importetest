<?php

namespace Drupal\Tests\commerce_migrate_commerce\Unit\Plugin\migrate\process\commerce1;

use Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\OrderItemDiscountAdjustment;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Rounder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\migrate\Row;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the order item discount adjustment plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\OrderItemDiscountAdjustment
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class OrderItemDiscountAdjustmentTest extends MigrateProcessTestCase {

  /**
   * The mocked migration.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $migration;

  /**
   * The mocked migration.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $migrationPluginManager;

  /**
   * The mocked migration.
   *
   * @var \Drupal\core\Entity\EntityTypeManager|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $entityTypeManager;

  /**
   * The mocked migration.
   *
   * @var \Drupal\commerce_price\Rounder|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $rounder;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->migration = $this->prophesize(Migration::class);
    $this->migrationPluginManager = $this->prophesize(MigrationPluginManager::class);
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->rounder = $this->prophesize(Rounder::class);
  }

  /**
   * Tests valid input.
   *
   * @dataProvider providerTestTransform
   */
  public function testTransform($value, $components, $shipping, $rounder, $expected) {
    $row = $this->prophesize(Row::class);
    $row->getSourceProperty('order_components/0/data/components')
      ->willReturn($components);
    $row->getSourceProperty('shipping')
      ->willReturn($shipping);
    $row->getSourceProperty('line_item_id')
      ->willReturn(2);
    $row->getSourceProperty('max_line_item_id')
      ->willReturn(1);
    $row->getSourceProperty('num_product_line')
      ->willReturn(1);

    $this->rounder->round($rounder)->willReturn($rounder);
    $this->rounder->round($rounder, PHP_ROUND_HALF_DOWN)->willReturn($rounder);

    $this->plugin = new TestOrderItemDiscountAdjustment([], 'test', [], $this->migration->reveal(), $this->migrationPluginManager->reveal(), $this->entityTypeManager->reveal(), $this->rounder->reveal());

    $result = $this->plugin->transform($value, $this->migrateExecutable, $row->reveal(), 'foo');
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testTransform().
   */
  public function providerTestTransform() {
    return [
      'no components' => [
        [
          'name' => 'tax|sample_michigan_sales_tax',
          'price' =>
            [
              'amount' => 288.0,
              'currency_code' => 'USD',
              'data' => [],
            ],
        ],
        [],
        [
          'data' => serialize([]),
        ],
        new Price(0, 'USD'),
        [],
      ],
      'not shipping and adjustment' => [
        [
          'name' => 'tax|sample_michigan_sales_tax',
          'price' =>
            [
              'amount' => 288.0,
              'currency_code' => 'USD',
              'data' => [
                'tax_rate' => [
                  'name' => 'A tax',
                  'display_title' => 'Sales tax',
                  'rate' => '0.05',
                  'type' => 'sales_tax',
                ],
              ],
            ],
          'included' => FALSE,
        ],
        [
          [
            'name' => 'base_price',
            'price' =>
              [
                'amount' => 4800.0,
                'currency_code' => 'USD',
                'data' => [],
              ],
            'included' => TRUE,
          ],
          [
            'name' => 'tax|sample_michigan_sales_tax',
            'price' =>
              [
                'amount' => 288.0,
                'currency_code' => 'USD',
                'data' => [
                  'tax_rate' => [
                    'name' => 'A tax',
                    'display_title' => 'Sales tax',
                    'rate' => '0.05',
                    'type' => 'sales_tax',
                  ],
                ],
              ],
            'included' => FALSE,
          ],
        ],
        [
          [
            'line_item_id' => '26',
            'data' => serialize([
              'shipping_service' =>
                [
                  'name' => 'express_shipping',
                  'price_component' => 'flat_rate_express_shipping',
                ],
            ]),
          ],
        ],
        new Price(2.88, 'USD'),
        [
          'amount' => '2.88',
          'currency_code' => 'USD',
          'percentage' => '0.05',
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
          'type' => 'tax',
          'label' => 'Sales tax',
        ],
      ],
      'shipping no adjustment' => [
        [
          'name' => 'tax|sample_michigan_sales_tax',
          'price' =>
            [
              'amount' => 288.0,
              'currency_code' => 'USD',
              'data' => [],
            ],
          'included' => FALSE,
        ],
        [
          [
            'name' => 'flat_rate_express_shipping',
            'price' =>
              [
                'amount' => 288.0,
                'currency_code' => 'USD',
                'data' => [],
              ],
            'included' => FALSE,
          ],
        ],
        [
          [
            'line_item_id' => '26',
            'data' => serialize([
              'shipping_service' =>
                [
                  'name' => 'express_shipping',
                  'price_component' => 'flat_rate_express_shipping',
                ],
            ]),
          ],
        ],
        new Price(0, 'USD'),
        [],
      ],
    ];
  }

  /**
   * Tests that exceptions are thrown as needed.
   *
   * @dataProvider providerTestInvalidValue
   */
  public function testInvalidValue($value, $shipping, $components, $message) {
    $row = $this->prophesize(Row::class);
    $row->getSourceProperty('order_components/0/data/components')
      ->willReturn($components);
    $row->getSourceProperty('shipping')
      ->willReturn($shipping);
    $row->getSourceProperty('line_item_id')
      ->willReturn(2);

    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage($message);

    $configuration = [];
    $this->plugin = new OrderItemDiscountAdjustment($configuration, 'test', [], $this->migration->reveal(), $this->migrationPluginManager->reveal(), $this->entityTypeManager->reveal(), $this->rounder->reveal());
    $this->plugin->transform($value, $this->migrateExecutable, $row->reveal(), 'foo');
  }

  /**
   * Data provider for testInvalidValue().
   */
  public function providerTestInvalidValue() {
    return [
      'test 0' => [
        [
          'name' => 'taxerror|sample_michigan_sales_tax',
          'price' =>
            [
              'amount' => 288.0,
              'currency_code' => 'USD',
              'data' => [],
            ],
          'included' => FALSE,
        ],
        [
          [
            'line_item_id' => '26',
            'data' => 'a:2:{s:16:"shipping_service";a:14:{s:4:"name";s:16:"express_shipping";s:4:"base";s:16:"express_shipping";s:13:"display_title";s:32:"Express shipping: 1 business day";s:11:"description";s:48:"An express shipping service with additional fee.";s:15:"shipping_method";s:9:"flat_rate";s:15:"rules_component";b:1;s:15:"price_component";s:26:"flat_rate_express_shipping";s:6:"weight";i:0;s:9:"callbacks";a:4:{s:4:"rate";s:37:"commerce_flat_rate_service_rate_order";s:12:"details_form";s:29:"express_shipping_details_form";s:21:"details_form_validate";s:38:"express_shipping_details_form_validate";s:19:"details_form_submit";s:36:"express_shipping_details_form_submit";}s:6:"module";s:18:"commerce_flat_rate";s:5:"title";s:16:"Express Shipping";s:9:"base_rate";a:3:{s:6:"amount";s:4:"1500";s:13:"currency_code";s:3:"USD";s:4:"data";a:0:{}}s:4:"data";a:0:{}s:10:"admin_list";b:1;}s:15:"service_details";a:0:{}}',
          ],
        ],
        [
          [
            'name' => 'taxerror|sample_michigan_sales_tax',
          ],
        ],
        "Unknown adjustment type for line item '2'",
      ],
    ];
  }

  /**
   * Tests getAdjustment method.
   *
   * @dataProvider providerTestGetAdjustment
   */
  public function testGetAdjustment($value, $last_line, $rounder_input, $rounder_output, $rounder_output_2, $split, $expected) {
    $num_product_line = 1;

    $row = $this->prophesize(Row::class);
    $row->getSourceProperty('line_item_id')
      ->willReturn(2);
    $row->getSourceProperty('max_line_item_id')
      ->willReturn(1);
    $row->getSourceProperty('num_product_line')
      ->willReturn($num_product_line);

    $this->rounder->round($rounder_input)->willReturn($rounder_output);
    $this->rounder->round($rounder_input, PHP_ROUND_HALF_DOWN)->willReturn($rounder_output_2);

    $this->plugin = new TestOrderItemDiscountAdjustment([], 'test', [], $this->migration->reveal(), $this->migrationPluginManager->reveal(), $this->entityTypeManager->reveal(), $this->rounder->reveal());

    $result = $this->plugin->getAdjustment($value, $this->migrateExecutable, $row->reveal());
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for testGetAdjustment().
   */
  public function providerTestGetAdjustment() {
    return [
      'not array' => [
        'not array',
        FALSE,
        NULL,
        NULL,
        NULL,
        NULL,
        [],
      ],
      'base price' => [
        [
          'name' => 'base_price',
        ],
        FALSE,
        NULL,
        NULL,
        NULL,
        NULL,
        [],
      ],
      'tax' => [
        [
          'name' => 'tax|sales_tax',
          'price' =>
            [
              'amount' => 1000.00,
              'currency_code' => 'USD',
              'data' => [
                'tax_rate' => [
                  'name' => 'A tax',
                  'display_title' => 'Sales tax',
                  'rate' => '0.05',
                  'type' => 'sales_tax',
                ],
              ],
            ],
          'included' => FALSE,
        ],
        FALSE,
        new Price(10.00, 'USD'),
        new Price(10.00, 'USD'),
        new Price(10.00, 'USD'),
        new Price(10.00, 'USD'),
        [
          'type' => 'tax',
          'label' => 'Sales tax',
          'amount' => '10',
          'currency_code' => 'USD',
          'percentage' => '0.05',
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ],
      ],
      'discount' => [
        [
          'name' => 'discount|arbor',
          'price' =>
            [
              'amount' => 2000,
              'currency_code' => 'USD',
              'data' => [
                'discount_component_title' => 'Arbor',
              ],
            ],
          'included' => FALSE,
        ],
        FALSE,
        new Price(20, 'USD'),
        new Price(20, 'USD'),
        new Price(20, 'USD'),
        new Price(20, 'USD'),
        [
          'type' => 'promotion',
          'label' => 'Arbor',
          'amount' => '20',
          'currency_code' => 'USD',
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Tests split method.
   *
   * @dataProvider providerTestSplit
   */
  public function testSplit($num_product_line, $last_line, $price, $split_rounder_input, $split_rounder_output, $expected) {
    $this->rounder->round($split_rounder_input, PHP_ROUND_HALF_DOWN)
      ->willReturn($split_rounder_output);

    $configuration = [];
    $this->plugin = new TestOrderItemDiscountAdjustment($configuration, 'map', [], $this->migration->reveal(), $this->migrationPluginManager->reveal(), $this->entityTypeManager->reveal(), $this->rounder->reveal());
    $result = $this->plugin->split($num_product_line, $last_line, $price);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testSplit().
   */
  public function providerTestSplit() {
    $zero_price = new Price(0, 'USD');
    $ten_price = new Price(10.00, 'USD');
    return [
      'no product line' => [
        0,
        FALSE,
        $zero_price,
        $zero_price,
        $zero_price,
        NULL,
      ],
      'one product line not last line' => [
        1,
        FALSE,
        $ten_price,
        $ten_price,
        $ten_price,
        $ten_price,
      ],
      'one product line last line' => [
        1,
        TRUE,
        $ten_price,
        $ten_price,
        $ten_price,
        $ten_price,
      ],
      'two product lines not last line' => [
        2,
        FALSE,
        $ten_price,
        new Price(5.00, 'USD'),
        new Price(5.00, 'USD'),
        new Price(5.00, 'USD'),
      ],
      'two product lines last line' => [
        2,
        TRUE,
        $ten_price,
        new Price(5.00, 'USD'),
        new Price(5.00, 'USD'),
        new Price(5.00, 'USD'),
      ],
      'three product lines not last line' => [
        3,
        FALSE,
        new Price(10.00, 'USD'),
        new Price(3.333333, 'USD'),
        new Price(3.33, 'USD'),
        new Price(3.33, 'USD'),
      ],
      'three product lines last line' => [
        3,
        TRUE,
        new Price(10.00, 'USD'),
        new Price(3.333333, 'USD'),
        new Price(3.33, 'USD'),
        new Price(3.34, 'USD'),
      ],
    ];
  }

}

/**
 * Test class for OrderItemDiscountAdjustment.
 */
class TestOrderItemDiscountAdjustment extends OrderItemDiscountAdjustment {

  /**
   * Sets the rounder for the test process plugin.
   */
  public function setRounder($rounder) {
    $this->rounder = $rounder;
  }

  /**
   * {@inheritdoc}
   */
  // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
  public function getAdjustment($value, $executable, Row $row) {
    return parent::getAdjustment($value, $executable, $row);
  }

  /**
   * {@inheritdoc}
   */
  // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
  public function split($num_product_line, $last_line, Price $price) {
    return parent::split($num_product_line, $last_line, $price);
  }

}
