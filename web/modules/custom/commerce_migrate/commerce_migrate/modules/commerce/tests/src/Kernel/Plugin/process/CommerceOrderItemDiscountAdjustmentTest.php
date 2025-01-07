<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Plugin\migrate\process\commerce1;

use Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\OrderItemDiscountAdjustment;
use Drupal\commerce_price\Rounder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Row;

/**
 * Tests the Order item discount process plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\OrderItemDiscountAdjustment
 *
 * @group commerce_migrate_commerce
 */
class CommerceOrderItemDiscountAdjustmentTest extends KernelTestBase {

  /**
   * The migrate executable.
   *
   * @var \Drupal\migrate\MigrateExecutable|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $migrateExecutable;

  /**
   * The process plugin being tested.
   *
   * @var \Drupal\migrate\Plugin\MigrateProcessInterface
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce',
    'commerce_price',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $migration = $this->createMock('Drupal\migrate\Plugin\MigrationInterface');
    $plugin_manager = $this->createMock('Drupal\migrate\Plugin\MigrationPluginManagerInterface');
    $this->migrateExecutable = $this->createMock('Drupal\migrate\MigrateExecutable');
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $rounder = new Rounder($entity_type_manager);
    $this->plugin = new OrderItemDiscountAdjustment([], 'map', [], $migration, $plugin_manager, $entity_type_manager, $rounder);
  }

  /**
   * Tests OrderItemDiscountAdjustment process plugin.
   *
   * @dataProvider providerOrderItemDiscountAdjustment
   */
  public function testOrderItemDiscountAdjustment($value, $row_values, $expected) {
    // Create currency.
    $currency_importer = $this->container->get('commerce_price.currency_importer');
    $currency_importer->import('NZD');

    $row = new Row();
    foreach ($row_values as $key => $data) {
      $row->setSourceProperty($key, $data);
    }
    $value = $this->plugin->transform($value, $this->migrateExecutable, $row, 'destinationproperty');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testOrderItemDiscountAdjustment().
   */
  public function providerOrderItemDiscountAdjustment() {
    // A base price value is not adjusted.
    $tests[0]['value'] = [
      'name' => 'base_price',
      'price' => [
        'amount' => '234',
        'currency_code' => 'NZD',
        'fraction_digits' => '2',
        'data' => [],
      ],
    ];
    // Row properties.
    $tests[0]['row_values'] = [
      'order_components/0/data/components' => unserialize('a:3:{i:0;a:3:{s:4:"name";s:10:"base_price";s:5:"price";a:3:{s:6:"amount";d:10200;s:13:"currency_code";s:3:"USD";s:4:"data";a:0:{}}s:8:"included";b:1;}i:1;a:3:{s:4:"name";s:30:"discount|discount_bag_discount";s:5:"price";a:3:{s:6:"amount";d:-320;s:13:"currency_code";s:3:"USD";s:4:"data";a:2:{s:13:"discount_name";s:21:"discount_bag_discount";s:24:"discount_component_title";s:12:"Bag discount";}}s:8:"included";b:1;}i:2;a:3:{s:4:"name";s:27:"other|discount_hat_discount";s:5:"price";a:3:{s:6:"amount";d:-375;s:13:"currency_code";s:3:"USD";s:4:"data";a:2:{s:13:"discount_name";s:21:"discount_hat_discount";s:24:"discount_component_title";s:12:"Hat discount";}}s:8:"included";b:1;}}'),
      'line_item_id' => '3',
      'num_product_line' => '2',
      'shipping' => [
        [
          'line_item_id' => '11',
          'order_id' => '1',
          'type' => 'shipping',
          'line_item_label' => 'Express shipping: 1 business day',
          'quantity' => '1.00',
          'data' => 'a:1:{s:16:"shipping_service";a:14:{s:4:"name";s:16:"express_shipping";s:4:"base";s:16:"express_shipping";s:13:"display_title";s:32:"Express shipping: 1 business day";s:11:"description";s:48:"An express shipping service with additional fee.";s:15:"shipping_method";s:9:"flat_rate";s:15:"rules_component";b:1;s:15:"price_component";s:26:"flat_rate_express_shipping";s:6:"weight";i:0;s:9:"callbacks";a:4:{s:4:"rate";s:37:"commerce_flat_rate_service_rate_order";s:12:"details_form";s:29:"express_shipping_details_form";s:21:"details_form_validate";s:38:"express_shipping_details_form_validate";s:19:"details_form_submit";s:36:"express_shipping_details_form_submit";}s:6:"module";s:18:"commerce_flat_rate";s:5:"title";s:16:"Express Shipping";s:9:"base_rate";a:3:{s:6:"amount";s:4:"1500";s:13:"currency_code";s:3:"USD";s:4:"data";a:0:{}}s:4:"data";a:0:{}s:10:"admin_list";b:1;}}',
        ],
      ],
    ];
    $tests[0]['expected'] = [];

    // A discount prorated over 2 product lines.
    $tests[1] = $tests[0];
    $tests[1]['value'] = [
      'name' => 'discount|discount_bag_discount',
      'price' => [
        'amount' => '1000',
        'currency_code' => 'NZD',
        'fraction_digits' => '2',
        'data' => [
          'discount_component_title' => 'test discount',
        ],
      ],
    ];
    $tests[1]['expected'] = [
      'type' => 'promotion',
      'label' => 'test discount',
      'amount' => '5.00',
      'currency_code' => 'NZD',
      'percentage' => NULL,
      'source_id' => 'custom',
      'included' => FALSE,
      'locked' => TRUE,
    ];

    // A discount prorated over 3 product lines.
    $tests[2] = $tests[0];
    $tests[2]['value'] = [
      'name' => 'discount|discount_bag_discount',
      'price' => [
        'amount' => '1000',
        'currency_code' => 'NZD',
        'fraction_digits' => '2',
        'data' => [
          'discount_component_title' => 'test discount',
        ],
      ],
    ];
    $tests[2]['row_values']['num_product_line'] = '3';
    $tests[2]['expected'] = [
      'type' => 'promotion',
      'label' => 'test discount',
      'amount' => '3.33',
      'currency_code' => 'NZD',
      'percentage' => NULL,
      'source_id' => 'custom',
      'included' => FALSE,
      'locked' => TRUE,
    ];

    // A discount with a discount amount of 0.
    $tests[3] = $tests[0];
    $tests[3]['value'] = [
      'name' => 'discount|discount_bag_discount',
      'price' => [
        'amount' => '0',
        'currency_code' => 'NZD',
        'fraction_digits' => '2',
        'data' => [
          'discount_component_title' => 'test discount',
        ],
      ],
    ];
    $tests[3]['row_values']['num_product_line'] = '3';
    $tests[3]['expected'] = [
      'type' => 'promotion',
      'label' => 'test discount',
      'amount' => '0.00',
      'currency_code' => 'NZD',
      'percentage' => NULL,
      'source_id' => 'custom',
      'included' => FALSE,
      'locked' => TRUE,
    ];
    return $tests;
  }

  /**
   * Tests OrderItemDiscountAdjustment process plugin.
   *
   * @dataProvider providerOrderItemDiscountAdjustmentSkip
   */
  public function testOrderItemDiscountAdjustmentSkip($value, $row_values) {
    $this->expectException(MigrateSkipRowException::class);
    $this->expectExceptionMessage((sprintf("Unknown adjustment type for line item '%s'", $row_values['line_item_id'])));

    $row = new Row();
    foreach ($row_values as $key => $data) {
      $row->setSourceProperty($key, $data);
    }
    $this->plugin->transform($value, $this->migrateExecutable, $row, 'destinationproperty');
  }

  /**
   * Data provider for testOrderItemDiscountAdjustment().
   */
  public function providerOrderItemDiscountAdjustmentSkip() {
    // A base price value in not adjusted.
    $tests[0]['value'] = [
      'name' => 'other|discount_hat_discount',
      'price' => [
        'amount' => '234',
        'currency_code' => 'NZD',
        'fraction_digits' => '2',
        'data' => [],
      ],
    ];
    // Row properties.
    $tests[0]['row_values'] = [
      'order_components/0/data/components' => unserialize('a:3:{i:0;a:3:{s:4:"name";s:10:"base_price";s:5:"price";a:3:{s:6:"amount";d:10200;s:13:"currency_code";s:3:"USD";s:4:"data";a:0:{}}s:8:"included";b:1;}i:1;a:3:{s:4:"name";s:30:"discount|discount_bag_discount";s:5:"price";a:3:{s:6:"amount";d:-320;s:13:"currency_code";s:3:"USD";s:4:"data";a:2:{s:13:"discount_name";s:21:"discount_bag_discount";s:24:"discount_component_title";s:12:"Bag discount";}}s:8:"included";b:1;}i:2;a:3:{s:4:"name";s:27:"other|discount_hat_discount";s:5:"price";a:3:{s:6:"amount";d:-375;s:13:"currency_code";s:3:"USD";s:4:"data";a:2:{s:13:"discount_name";s:21:"discount_hat_discount";s:24:"discount_component_title";s:12:"Hat discount";}}s:8:"included";b:1;}}'),
      'line_item_id' => '3',
      'shipping' => [
        [
          'line_item_id' => '11',
          'order_id' => '1',
          'type' => 'shipping',
          'line_item_label' => 'Express shipping: 1 business day',
          'quantity' => '1.00',
          'data' => 'a:1:{s:16:"shipping_service";a:14:{s:4:"name";s:16:"express_shipping";s:4:"base";s:16:"express_shipping";s:13:"display_title";s:32:"Express shipping: 1 business day";s:11:"description";s:48:"An express shipping service with additional fee.";s:15:"shipping_method";s:9:"flat_rate";s:15:"rules_component";b:1;s:15:"price_component";s:26:"flat_rate_express_shipping";s:6:"weight";i:0;s:9:"callbacks";a:4:{s:4:"rate";s:37:"commerce_flat_rate_service_rate_order";s:12:"details_form";s:29:"express_shipping_details_form";s:21:"details_form_validate";s:38:"express_shipping_details_form_validate";s:19:"details_form_submit";s:36:"express_shipping_details_form_submit";}s:6:"module";s:18:"commerce_flat_rate";s:5:"title";s:16:"Express Shipping";s:9:"base_rate";a:3:{s:6:"amount";s:4:"1500";s:13:"currency_code";s:3:"USD";s:4:"data";a:0:{}}s:4:"data";a:0:{}s:10:"admin_list";b:1;}}',
        ],
      ],
    ];

    return $tests;
  }

}
