<?php

namespace Drupal\Tests\commerce_migrate_shopify\Kernel\Migrate\shopify;

use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;
use Drupal\Tests\commerce_migrate\Kernel\CsvTestBase;

/**
 * Tests product variation migration.
 *
 * @requires module migrate_plus
 * @requires module migrate_source_csv
 *
 * @group commerce_migrate
 * @group commerce_migrate_shopify
 */
class ProductVariationTest extends CsvTestBase {

  use CommerceMigrateTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'action',
    'address',
    'commerce',
    'commerce',
    'commerce_migrate',
    'commerce_migrate_shopify',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'entity',
    'field',
    'inline_entity_form',
    'migrate_plus',
    'options',
    'path',
    'system',
    'text',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected $fixtures = __DIR__ . '/../../../fixtures/csv/shopify-products_export_test.csv';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('user', ['users_data']);
    // Make sure uid 1 is created.
    user_install();

    $this->installConfig('commerce_product');
    $this->installEntitySchema('commerce_store');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_product');
    $this->executeMigrations([
      'shopify_product_variation_type',
      'shopify_product_variation',
    ]);
  }

  /**
   * Test product variation migration.
   */
  public function testProductVariation() {
    $variation = [
      'id' => 1,
      'type' => 'bag_accessory',
      'uid' => '1',
      'sku' => 'THEB15--1-Size',
      'price' => '30.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 2,
      'type' => 'bag_accessory',
      'uid' => '1',
      'sku' => 'YMB01-Green--1-Size',
      'price' => '18.950000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 3,
      'type' => 'bag_accessory',
      'uid' => '1',
      'sku' => 'YMB01-Yellow--1-Size',
      'price' => '18.950000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 4,
      'type' => 'mens_short_sleeve_t_shirts',
      'uid' => '1',
      'sku' => 'YGS08-White--1-Size',
      'price' => '12.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 5,
      'type' => 'mens_t_shirt',
      'uid' => '1',
      'sku' => 'MT01-Brick--M',
      'price' => '18.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 12,
      'type' => 'mens_t_shirt',
      'uid' => '1',
      'sku' => 'MT01-Gray--XXL',
      'price' => '18.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
  }

}
