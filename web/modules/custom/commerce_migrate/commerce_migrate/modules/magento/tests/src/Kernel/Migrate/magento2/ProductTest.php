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
class ProductTest extends CsvTestBase {

  use CommerceMigrateTestTrait;

  /**
   * File path of the test fixture.
   *
   * @var string
   */
  protected $fixtures = __DIR__ . '/../../../../fixtures/csv/magento2-catalog_product_20180326_013553_test.csv';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'action',
    'address',
    'entity',
    'field',
    'inline_entity_form',
    'migrate_plus',
    'options',
    'path',
    'path_alias',
    'system',
    'text',
    'user',
    'views',
    'commerce',
    'commerce_price',
    'commerce_store',
    'commerce',
    'commerce_product',
    'commerce_migrate',
    'commerce_migrate_magento',
  ];

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
    $this->installEntitySchema('path_alias');
    $this->installConfig('commerce_product');
    $this->createDefaultStore();
    $this->executeMigrations([
      'magento2_product_variation_type',
      'magento2_product_variation',
      'magento2_product_type',
      'magento2_product',
    ]);
  }

  /**
   * Test product migration.
   */
  public function testProduct() {
    $this->assertProductEntity(1, 'bag', '1', 'Joust Duffle Bag', TRUE, ['1'], ['1']);
    $variation = [
      'id' => 1,
      'type' => 'bag',
      'uid' => '1',
      'sku' => '24-MB01',
      'price' => '34.000000',
      'currency' => 'USD',
      'product_id' => '1',
      'title' => 'Joust Duffle Bag',
      'order_item_type_id' => 'default',
      'created_time' => '1521962400',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $this->assertProductEntity(7, 'gear', '1', 'Sprite Foam Roller', TRUE, ['1'], ['7']);
    $variation = [
      'id' => 7,
      'type' => 'gear',
      'uid' => '1',
      'sku' => '24-WG088',
      'price' => '19.000000',
      'currency' => 'USD',
      'product_id' => '7',
      'title' => 'Sprite Foam Roller',
      'order_item_type_id' => 'default',
      'created_time' => '1521962400',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $this->assertProductEntity(8, 'sprite_stasis_ball', '1', 'Sprite Stasis Ball 55 cm', TRUE, ['1'], ['8']);
    $variation = [
      'id' => 8,
      'type' => 'sprite_stasis_ball',
      'uid' => '1',
      'sku' => '24-WG081-gray',
      'price' => '23.000000',
      'currency' => 'USD',
      'product_id' => '8',
      'title' => 'Sprite Stasis Ball 55 cm',
      'order_item_type_id' => 'default',
      'created_time' => '1521962400',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
  }

}
