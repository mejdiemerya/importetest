<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Price;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests order item migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class OrderItemTest extends Commerce1TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_number_pattern',
    'commerce_store',
    'commerce_order',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'migrate_plus',
    'path',
    'path_alias',
    'profile',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->migrateOrderItems();
  }

  /**
   * Test line item migration from Drupal 7 to 8.
   */
  public function testOrderItem() {
    $order_item = [
      'id' => 1,
      'order_id' => NULL,
      'created' => '1493287435',
      'changed' => '1493287440',
      'purchased_entity_id' => '12',
      'quantity' => '1.00',
      'title' => 'Hat 2',
      'unit_price' => '12.000000',
      'unit_price_currency_code' => 'USD',
      'total_price' => '12.000000',
      'total_price_currency_code' => 'USD',
      'uses_legacy_adjustments' => FALSE,
      'adjustments' => [],
    ];
    $this->assertOrderItem($order_item);

    $order_item = [
      'id' => 2,
      'order_id' => NULL,
      'created' => '1493287445',
      'changed' => '1493287450',
      'purchased_entity_id' => '12',
      'quantity' => '1.00',
      'title' => 'Hat 2',
      'unit_price' => '12.000000',
      'unit_price_currency_code' => 'USD',
      'total_price' => '12.000000',
      'total_price_currency_code' => 'USD',
      'uses_legacy_adjustments' => FALSE,
      'adjustments' => [],
    ];
    $this->assertOrderItem($order_item);

    $order_item = [
      'id' => 3,
      'order_id' => NULL,
      'created' => '1493287455',
      'changed' => '1493287460',
      'purchased_entity_id' => '45',
      'quantity' => '1.00',
      'title' => 'Tshirt 3',
      'unit_price' => '38.000000',
      'unit_price_currency_code' => 'USD',
      'total_price' => '38.000000',
      'total_price_currency_code' => 'USD',
      'uses_legacy_adjustments' => FALSE,
      'adjustments' => [],
    ];
    $this->assertOrderItem($order_item);

    // No shipping line items.
    $this->assertNull(OrderItem::load(11));
    $this->assertNull(OrderItem::load(12));
    $this->assertNull(OrderItem::load(13));

    $order_item = [
      'id' => 14,
      'order_id' => NULL,
      'created' => '1541732400',
      'changed' => '1541732476',
      'purchased_entity_id' => 10,
      'quantity' => '3.00',
      'title' => 'Water Bottle 1',
      'unit_price' => '16.000000',
      'unit_price_currency_code' => 'USD',
      'total_price' => '48.000000',
      'total_price_currency_code' => 'USD',
      'uses_legacy_adjustments' => FALSE,
      'adjustments' => [
        new Adjustment([
          'type' => 'promotion',
          'label' => 'Peace day discount',
          'amount' => new Price('-24', 'USD'),
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
        new Adjustment([
          'type' => 'tax',
          'label' => 'Sample NZ Sales Tax 6%',
          'amount' => new Price('2.88', 'USD'),
          'percentage' => '0.06',
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
      ],
    ];
    $this->assertOrderItem($order_item);

    // No commerce_discount line items.
    $this->assertNull(OrderItem::load(18));
    $this->assertNull(OrderItem::load(27));

    $order_item = [
      'id' => 28,
      'order_id' => NULL,
      'created' => '1544649230',
      'changed' => '1544649300',
      'purchased_entity_id' => 1,
      'quantity' => '10.00',
      'title' => 'Tote Bag 1',
      'unit_price' => '16.000000',
      'unit_price_currency_code' => 'USD',
      'total_price' => '160.000000',
      'total_price_currency_code' => 'USD',
      'uses_legacy_adjustments' => FALSE,
      'adjustments' => [
        new Adjustment([
          'type' => 'promotion',
          'label' => 'Bag discount',
          'amount' => new Price('-10.67', 'USD'),
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
        new Adjustment([
          'type' => 'tax',
          'label' => 'Sample NZ Sales Tax 6%',
          'amount' => new Price('3.77', 'USD'),
          'percentage' => '0.06',
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
        new Adjustment([
          'type' => 'promotion',
          'label' => 'Hat discount',
          'amount' => new Price('-1.25', 'USD'),
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
      ],
    ];
    $this->assertOrderItem($order_item);

    $order_item = [
      'id' => 29,
      'order_id' => NULL,
      'created' => '1544649280',
      'changed' => '1544649280',
      'purchased_entity_id' => 10,
      'quantity' => '1.00',
      'title' => 'Water Bottle 1',
      'unit_price' => '16.000000',
      'unit_price_currency_code' => 'USD',
      'total_price' => '16.000000',
      'total_price_currency_code' => 'USD',
      'uses_legacy_adjustments' => FALSE,
      'adjustments' => [
        new Adjustment([
          'type' => 'promotion',
          'label' => 'Bag discount',
          'amount' => new Price('-10.67', 'USD'),
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
        new Adjustment([
          'type' => 'tax',
          'label' => 'Sample NZ Sales Tax 6%',
          'amount' => new Price('3.77', 'USD'),
          'percentage' => '0.06',
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
        new Adjustment([
          'type' => 'promotion',
          'label' => 'Hat discount',
          'amount' => new Price('-1.25', 'USD'),
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
      ],
    ];
    $this->assertOrderItem($order_item);

    $order_item = [
      'id' => 31,
      'order_id' => NULL,
      'created' => '1551997430',
      'changed' => '1551997441',
      'purchased_entity_id' => 11,
      'quantity' => '3.00',
      'title' => 'Hat 1',
      'unit_price' => '16.000000',
      'unit_price_currency_code' => 'USD',
      'total_price' => '48.000000',
      'total_price_currency_code' => 'USD',
      'uses_legacy_adjustments' => FALSE,
      'adjustments' => [
        new Adjustment([
          'type' => 'promotion',
          'label' => 'Bag discount',
          'amount' => new Price('-10.66', 'USD'),
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
        new Adjustment([
          'type' => 'tax',
          'label' => 'Sample NZ Sales Tax 6%',
          'amount' => new Price('3.76', 'USD'),
          'percentage' => '0.06',
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
        new Adjustment([
          'type' => 'promotion',
          'label' => 'Hat discount',
          'amount' => new Price('-1.25', 'USD'),
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ]),
      ],
    ];
    $this->assertOrderItem($order_item);
  }

}
