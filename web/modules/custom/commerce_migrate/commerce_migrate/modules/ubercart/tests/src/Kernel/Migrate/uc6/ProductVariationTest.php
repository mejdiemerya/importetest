<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests Product variation migration.
 *
 * @requires module migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class ProductVariationTest extends Ubercart6TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'content_translation',
    'language',
    'menu_ui',
    'migrate_plus',
    'path',
    'path_alias',
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
      'sku' => 'towel-bath-001',
      'price' => '20.000000',
      'currency' => 'NZD',
      'product_id' => '1',
      'title' => 'Bath Towel',
      'order_item_type_id' => 'default',
      'created_time' => '1492867780',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 2,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'towel-beach-001',
      'price' => '15.000000',
      'currency' => 'NZD',
      'product_id' => '2',
      'title' => 'Beach Towel',
      'order_item_type_id' => 'default',
      'created_time' => '1492989418',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 3,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'Fairy-Cake-001',
      'price' => '1500.000000',
      'currency' => 'NZD',
      'product_id' => '3',
      'title' => 'Fairy cake',
      'order_item_type_id' => 'default',
      'created_time' => '1492989703',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 4,
      'type' => 'ship',
      'uid' => '1',
      'sku' => 'ship-001',
      'price' => '6000000000.000000',
      'currency' => 'NZD',
      'product_id' => '4',
      'title' => 'Golgafrincham B-Ark',
      'order_item_type_id' => 'default',
      'created_time' => '1500868190',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 5,
      'type' => 'ship',
      'uid' => '1',
      'sku' => 'ship-002',
      'price' => '123000000.000000',
      'currency' => 'NZD',
      'product_id' => '5',
      'title' => 'Heart of Gold',
      'order_item_type_id' => 'default',
      'created_time' => '1500868361',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $variation = ProductVariation::load(6);
    $this->assertNull($variation);
    $variation = ProductVariation::load(7);
    $this->assertNull($variation);
  }

}
