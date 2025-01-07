<?php

namespace Drupal\Tests\commerce_migrate_magento\Kernel\Migrate\magento2;

use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;
use Drupal\Tests\commerce_migrate\Kernel\CsvTestBase;

/**
 * Tests Product migration.
 *
 * @requires module migrate_plus
 * @requires module migrate_source_csv
 *
 * @group commerce_migrate
 * @group commerce_migrate_magento2
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
    'commerce_migrate_magento',
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
  protected $fixtures = __DIR__ . '/../../../../fixtures/csv/magento2-catalog_product_20180326_013553_test.csv';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('user', ['users_data']);
    // Make sure uid 1 is created.
    user_install();

    $this->installEntitySchema('commerce_store');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_product');
    $this->installConfig('commerce_product');
    $this->executeMigrations([
      'magento2_product_variation_type',
      'magento2_product_variation',
    ]);
  }

  /**
   * Test product variation migration.
   */
  public function testProductVariation() {
    $variation = [
      'id' => 1,
      'type' => 'bag',
      'uid' => '1',
      'sku' => '24-MB01',
      'price' => '34.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Joust Duffle Bag',
      'order_item_type_id' => 'default',
      'created_time' => '1521962400',
      'changed_time' => '1521962400',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 2,
      'type' => 'bag',
      'uid' => '1',
      'sku' => '24-MB02',
      'price' => '59.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Fusion Backpack',
      'order_item_type_id' => 'default',
      'created_time' => '1521962400',
      'changed_time' => '1521962400',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 3,
      'type' => 'bag',
      'uid' => '1',
      'sku' => '24-UB02',
      'price' => '74.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Impulse Duffle',
      'order_item_type_id' => 'default',
      'created_time' => '1521962400',
      'changed_time' => '1521962400',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $variation = [
      'id' => 4,
      'type' => 'bag',
      'uid' => '1',
      'sku' => '24-WB01',
      'price' => '32.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Voyage Yoga Bag',
      'order_item_type_id' => 'default',
      'created_time' => '1521962400',
      'changed_time' => '1521962400',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 5,
      'type' => 'bag',
      'uid' => '1',
      'sku' => '24-WB02',
      'price' => '32.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Compete Track Tote',
      'order_item_type_id' => 'default',
      'created_time' => '1521962400',
      'changed_time' => '1521962400',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    // Test a product with a fractional price.
    $variation = [
      'id' => 31,
      'type' => 'bottom',
      'uid' => '1',
      'sku' => 'MSH02-32-Black',
      'price' => '32.500000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Apollo Running Short-32-Black',
      'order_item_type_id' => 'default',
      'created_time' => '1521962520',
      'changed_time' => '1521962520',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
  }

}
