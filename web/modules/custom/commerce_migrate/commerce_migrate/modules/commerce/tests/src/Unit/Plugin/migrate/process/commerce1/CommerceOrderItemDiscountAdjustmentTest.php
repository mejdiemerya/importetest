<?php

namespace Drupal\Tests\commerce_migrate_commerce\Unit\Plugin\migrate\process\commerce1;

use Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\OrderItemDiscountAdjustment;
use Drupal\commerce_price\Price;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the Commerce Price plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\OrderItemDiscountAdjustment
 *
 * @group commerce_migrate_commerce
 */
class CommerceOrderItemDiscountAdjustmentTest extends MigrateProcessTestCase {

  /**
   * The rounder.
   *
   * @var \Drupal\commerce_price\RounderInterface
   */
  protected $rounder;

  /**
   * Tests Commerce Price plugin.
   *
   * @dataProvider providerOrderItemDiscountAdjustment
   */
  public function testOrderItemDiscountAdjustment($value = NULL, $components = NULL, $shipping = NULL, $expected = NULL) {
    $configuration = [];
    $migration = $this->createMock('Drupal\migrate\Plugin\MigrationInterface');
    $plugin_manager = $this->createMock('Drupal\migrate\Plugin\MigrationPluginManagerInterface');
    $entity_type_manager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $this->rounder = $this->createMock('\Drupal\commerce_price\RounderInterface');
    $this->row->expects($this->at(0))
      ->method('getSourceProperty')
      ->with('order_components/0/data/components')
      ->will($this->returnValue($components));

    $this->row->expects($this->at(1))
      ->method('getSourceProperty')
      ->with('shipping')
      ->will($this->returnValue($shipping));

    $price = new Price('10', 'NZD');
    $this->rounder->expects($this->at(0))
      ->method('round')
      ->with($price)
      ->will($this->returnValue($price));

    $this->plugin = new OrderItemDiscountAdjustment($configuration, 'map', [], $migration, $plugin_manager, $entity_type_manager, $this->rounder);

    $value = $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'destinationproperty');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testOrderItemDiscountAdjustment().
   */
  public function providerOrderItemDiscountAdjustment() {
    $tests[0]['value'] = [
      'name' => 'base_price',
      'price' => [
        'amount' => '234',
        'currency_code' => 'NZD',
        'fraction_digits' => '2',
        'data' => [],
      ],
    ];
    $tests[0]['value'] = [
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
    $tests[0]['components'] = unserialize('a:3:{i:0;a:3:{s:4:"name";s:10:"base_price";s:5:"price";a:3:{s:6:"amount";d:10200;s:13:"currency_code";s:3:"USD";s:4:"data";a:0:{}}s:8:"included";b:1;}i:1;a:3:{s:4:"name";s:30:"discount|discount_bag_discount";s:5:"price";a:3:{s:6:"amount";d:-320;s:13:"currency_code";s:3:"USD";s:4:"data";a:2:{s:13:"discount_name";s:21:"discount_bag_discount";s:24:"discount_component_title";s:12:"Bag discount";}}s:8:"included";b:1;}i:2;a:3:{s:4:"name";s:30:"discount|discount_hat_discount";s:5:"price";a:3:{s:6:"amount";d:-375;s:13:"currency_code";s:3:"USD";s:4:"data";a:2:{s:13:"discount_name";s:21:"discount_hat_discount";s:24:"discount_component_title";s:12:"Hat discount";}}s:8:"included";b:1;}}');
    $tests[0]['shipping'] = [
      [
        'line_item_id' => '11',
        'order_id' => '1',
        'type' => 'shipping',
        'line_item_label' => 'Express shipping: 1 business day',
        'quantity' => '1.00',
        'data' => 'a:1:{s:16:"shipping_service";a:14:{s:4:"name";s:16:"express_shipping";s:4:"base";s:16:"express_shipping";s:13:"display_title";s:32:"Express shipping: 1 business day";s:11:"description";s:48:"An express shipping service with additional fee.";s:15:"shipping_method";s:9:"flat_rate";s:15:"rules_component";b:1;s:15:"price_component";s:26:"flat_rate_express_shipping";s:6:"weight";i:0;s:9:"callbacks";a:4:{s:4:"rate";s:37:"commerce_flat_rate_service_rate_order";s:12:"details_form";s:29:"express_shipping_details_form";s:21:"details_form_validate";s:38:"express_shipping_details_form_validate";s:19:"details_form_submit";s:36:"express_shipping_details_form_submit";}s:6:"module";s:18:"commerce_flat_rate";s:5:"title";s:16:"Express Shipping";s:9:"base_rate";a:3:{s:6:"amount";s:4:"1500";s:13:"currency_code";s:3:"USD";s:4:"data";a:0:{}}s:4:"data";a:0:{}s:10:"admin_list";b:1;}}',
      ],
    ];
    $tests[0]['expected'] = [];

    return $tests;
  }

}
