<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc7;

use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests Product variation migration.
 *
 * @requires module migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
 */
class ProductVariationTest extends Ubercart7TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'comment',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'filter',
    'image',
    'menu_ui',
    'migrate_plus',
    'node',
    'path',
    'path_alias',
    'taxonomy',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->migrateProductVariations();
  }

  /**
   * Test product variation migration.
   */
  public function testProductVariation() {
    $variation = [
      'id' => 1,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'drink-001',
      'price' => '50.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Breshtanti ale',
      'order_item_type_id' => 'default',
      'created_time' => '1493289860',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 2,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'drink-002',
      'price' => '100.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Romulan ale',
      'order_item_type_id' => 'default',
      'created_time' => '1493326300',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 3,
      'type' => 'entertainment',
      'uid' => '1',
      'sku' => 'Holosuite-001',
      'price' => '40.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Holosuite 1',
      'order_item_type_id' => 'default',
      'created_time' => '1493326429',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
  }

}
