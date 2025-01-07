<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests product variation migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class ProductVariationTest extends Commerce1TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'comment',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'datetime',
    'file',
    'image',
    'link',
    'menu_ui',
    'migrate_plus',
    'node',
    'path',
    'path_alias',
    'profile',
    'system',
    'taxonomy',
    'telephone',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_attribute');
    $this->installEntitySchema('commerce_product_attribute_value');
    $this->installEntitySchema('commerce_product_variation');
    $this->installSchema('comment', ['comment_entity_statistics']);
    $this->installEntitySchema('file');
    $this->installEntitySchema('node');
    $this->installEntitySchema('profile');
    $this->installEntitySchema('taxonomy_term');

    $this->installConfig(['node']);
    $this->installConfig(['commerce_product']);

    $this->migrateFiles();
    $this->migrateFields();
    $this->executeMigrations([
      'commerce1_product_variation_type',
      'commerce1_product_type',
      'commerce1_product_attribute',
      'd7_taxonomy_term',
    ]);
    $this->migrateProductVariations();
  }

  /**
   * Test product variation migration from Drupal 7 Commerce to Drupal 8.
   */
  public function testProductVariation() {
    $variation = [
      'id' => 1,
      'type' => 'bags_cases',
      'uid' => '1',
      'sku' => 'TOT1-GRN-OS',
      'price' => '16.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Tote Bag 1',
      'order_item_type_id' => 'product',
      'created_time' => '1493287314',
      'changed_time' => '1493287350',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 11,
      'type' => 'hats',
      'uid' => '1',
      'sku' => 'HAT1-GRY-OS',
      'price' => '16.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Hat 1',
      'order_item_type_id' => 'product',
      'created_time' => '1493287364',
      'changed_time' => '1493287400',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 12,
      'type' => 'hats',
      'uid' => '1',
      'sku' => 'HAT2-BLK-OS',
      'price' => '12.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Hat 2',
      'order_item_type_id' => 'product',
      'created_time' => '1493287369',
      'changed_time' => '1493287405',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 12,
      'type' => 'hats',
      'uid' => '1',
      'sku' => 'HAT2-BLK-OS',
      'price' => '12.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Hat 2',
      'order_item_type_id' => 'product',
      'created_time' => '1493287369',
      'changed_time' => '1493287405',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 19,
      'type' => 'shoes',
      'uid' => '1',
      'sku' => 'SHO2-PRL-04',
      'price' => '40.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Shoe 2',
      'order_item_type_id' => 'product',
      'created_time' => '1493287404',
      'changed_time' => '1493287440',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 20,
      'type' => 'shoes',
      'uid' => '1',
      'sku' => 'SHO2-PRL-05',
      'price' => '40.000000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Shoe 2',
      'order_item_type_id' => 'product',
      'created_time' => '1493287409',
      'changed_time' => '1493287445',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 28,
      'type' => 'storage_devices',
      'uid' => '1',
      'sku' => 'USB-BLU-08',
      'price' => '11.990000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Storage 1',
      'order_item_type_id' => 'product',
      'created_time' => '1493287449',
      'changed_time' => '1493287485',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 29,
      'type' => 'storage_devices',
      'uid' => '1',
      'sku' => 'USB-BLU-16',
      'price' => '17.990000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Storage 1',
      'order_item_type_id' => 'product',
      'created_time' => '1493287454',
      'changed_time' => '1493287490',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $variation = [
      'id' => 30,
      'type' => 'storage_devices',
      'uid' => '1',
      'sku' => 'USB-BLU-32',
      'price' => '29.990000',
      'currency' => 'USD',
      'product_id' => NULL,
      'title' => 'Storage 1',
      'order_item_type_id' => 'product',
      'created_time' => '1493287459',
      'changed_time' => '1493287495',
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    // Test values of a product variation field.
    $variation = ProductVariation::load(1);
    $this->assertInstanceOf(ProductVariation::class, $variation);
    $expected = [
      [
        'target_id' => '1',
        'alt' => NULL,
        'title' => NULL,
        'width' => '860',
        'height' => '842',
      ],
      [
        'target_id' => '2',
        'alt' => NULL,
        'title' => NULL,
        'width' => '860',
        'height' => '1251',
      ],
      [
        'target_id' => '3',
        'alt' => NULL,
        'title' => NULL,
        'width' => '860',
        'height' => '1100',
      ],
    ];
    $actual = $variation->get('field_images')->getValue();
    $this->assertCount(3, $actual);
    $target_id = array_column($actual, 'target_id');
    array_multisort($target_id, SORT_ASC, SORT_NUMERIC, $actual);
    $this->assertSame($expected, $actual);
  }

}
