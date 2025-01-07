<?php

namespace Drupal\Tests\commerce_migrate_csv_example\Kernel\Migrate;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;
use Drupal\Tests\commerce_migrate\Kernel\CsvTestBase;

/**
 * Tests Product migration.
 *
 * @requires module migrate_plus
 * @requires module migrate_source_csv
 *
 * @group commerce_migrate
 * @group commerce_migrate_csv_example
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
    'commerce_migrate_csv_example',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'entity',
    'field',
    'file',
    'image',
    'inline_entity_form',
    'migrate_plus',
    'migrate_source_csv',
    'options',
    'path',
    'system',
    'taxonomy',
    'text',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected $fixtures = [__DIR__ . '/../../../fixtures/csv/example-products.csv'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_attribute');
    $this->installEntitySchema('commerce_product_attribute_value');
    $this->installConfig('commerce_product');

    $this->fs = \Drupal::service('file_system');
    $this->installEntitySchema('user');
    // Copy the source files.
    $this->fileMigrationSetup(__DIR__ . '/../../../fixtures/images');
    $this->createAttribute(['Accessory Size', 'Color', 'Shoe Size', 'Size']);

    $images = ['product_image', 'product_image_2', 'product_image_3'];
    foreach ($images as $image) {
      $field_name = 'field_' . $image;
      $field_storage_definition = [
        'field_name' => $field_name,
        'entity_type' => 'commerce_product_variation',
        'type' => 'image',
        'cardinality' => 1,
      ];
      $storage = FieldStorageConfig::create($field_storage_definition);
      $storage->save();

      $field_instance = [
        'field_name' => $field_name,
        'entity_type' => 'commerce_product_variation',
        'bundle' => 'default',
        'label' => $image,
        'settings' => [
          'handler' => 'default:file',
        ],
      ];
      $field = FieldConfig::create($field_instance);
      $field->save();
    }
  }

  /**
   * Test product variation migration from CSV source file.
   */
  public function testProductVariation() {
    $this->executeMigrations([
      'csv_example_attribute_value',
      'csv_example_image',
      'csv_example_product_variation',
    ]);

    // Set the attribute and files array for testing. Before each variation
    // test these are modified as needed for that variation.
    $attributes = [
      'attribute_color' =>
        [
          'id' => '16',
          'value' => 'Black',
        ],
      'attribute_size' =>
        [
          'id' => '18',
          'value' => 'XS',
        ],
      'attribute_accessory_size' =>
        [
          'id' => '15',
          'value' => NULL,
        ],
      'attribute_shoe_size' =>
        [
          'id' => '17',
          'value' => NULL,
        ],
    ];

    $files = [
      'field_product_image' =>
        [
          'target_id' => '1',
          'alt' => '',
          'title' => '',
          'width' => '322',
          'height' => '156',
        ],
      'field_product_image_2' =>
        [
          'target_id' => '2',
          'alt' => '',
          'title' => '',
          'width' => '211',
          'height' => '239',
        ],
      'field_product_image_3' => [],
    ];
    $variation = [
      'id' => 1,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-058',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(1, $attributes, $files);

    $attributes['attribute_size']['id'] = '19';
    $attributes['attribute_size']['value'] = 'SM';
    $files['field_product_image'] =
      [
        'target_id' => '6',
        'alt' => '',
        'title' => '',
        'width' => '322',
        'height' => '156',
      ];
    $files['field_product_image_2'] = [];
    $variation = [
      'id' => 2,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-059',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(2, $attributes, $files);

    $attributes['attribute_size']['id'] = '20';
    $attributes['attribute_size']['value'] = 'MD';
    $files['field_product_image'] =
      [
        'target_id' => '7',
        'alt' => '',
        'title' => '',
        'width' => '225',
        'height' => '225',
      ];
    $variation = [
      'id' => 3,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-060',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(3, $attributes, $files);

    $attributes['attribute_size']['id'] = '21';
    $attributes['attribute_size']['value'] = 'LG';
    $files['field_product_image']['target_id'] = '8';
    $variation = [
      'id' => 4,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-061',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(4, $attributes, $files);

    $attributes['attribute_size']['id'] = '22';
    $attributes['attribute_size']['value'] = 'XL';
    $files['field_product_image'] =
      [
        'target_id' => '9',
        'alt' => '',
        'title' => '',
        'width' => '322',
        'height' => '156',
      ];
    $variation = [
      'id' => 5,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-062',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(5, $attributes, $files);

    $attributes['attribute_size']['id'] = '23';
    $attributes['attribute_size']['value'] = '2XL';
    $files['field_product_image'] =
      [
        'target_id' => '10',
        'alt' => '',
        'title' => '',
        'width' => '225',
        'height' => '225',
      ];
    $variation = [
      'id' => 6,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-063',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(6, $attributes, $files);

    $attributes['attribute_size']['id'] = '24';
    $attributes['attribute_size']['value'] = '3XL';
    $files['field_product_image'] =
      [
        'target_id' => '11',
        'alt' => '',
        'title' => '',
        'width' => '322',
        'height' => '156',
      ];
    $variation = [
      'id' => 7,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-064',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(7, $attributes, $files);

    $attributes['attribute_color']['id'] = '25';
    $attributes['attribute_color']['value'] = 'Blue';
    $attributes['attribute_size']['id'] = '18';
    $attributes['attribute_size']['value'] = 'XS';
    $files['field_product_image']['target_id'] = '12';
    $variation = [
      'id' => 8,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-065',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(8, $attributes, $files);

    $attributes['attribute_color']['id'] = '16';
    $attributes['attribute_color']['value'] = 'Black';
    $files['field_product_image'] =
      [
        'target_id' => '13',
        'alt' => '',
        'title' => '',
        'width' => '225',
        'height' => '225',
      ];
    $variation = [
      'id' => 9,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-072',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(9, $attributes, $files);

    $attributes['attribute_size']['id'] = '19';
    $attributes['attribute_size']['value'] = 'SM';
    $files['field_product_image']['target_id'] = '14';
    $variation = [
      'id' => 10,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-073',
      'price' => '349.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(10, $attributes, $files);

    $attributes['attribute_size']['id'] = '24';
    $attributes['attribute_size']['value'] = '3XL';
    $files['field_product_image'] =
      [
        'target_id' => '15',
        'alt' => '',
        'title' => '',
        'width' => '322',
        'height' => '156',
      ];
    $variation = [
      'id' => 11,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-080',
      'price' => '349.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(11, $attributes, $files);

    $attributes['attribute_color']['id'] = '16';
    $attributes['attribute_color']['value'] = 'Black';
    $attributes['attribute_size']['id'] = '26';
    $attributes['attribute_size']['value'] = '4';
    $files['field_product_image']['target_id'] = '16';
    $variation = [
      'id' => 12,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-081',
      'price' => '399.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(12, $attributes, $files);

    $attributes['attribute_color']['id'] = '27';
    $attributes['attribute_color']['value'] = 'Purple';
    $attributes['attribute_size']['id'] = '28';
    $attributes['attribute_size']['value'] = '6';
    $files['field_product_image'] =
      [
        'target_id' => '17',
        'alt' => '',
        'title' => '',
        'width' => '225',
        'height' => '225',
      ];
    $variation = [
      'id' => 13,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-088',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(13, $attributes, $files);

    $attributes['attribute_color']['id'] = '16';
    $attributes['attribute_color']['value'] = 'Black';
    $attributes['attribute_size']['id'] = '29';
    $attributes['attribute_size']['value'] = '8';
    $files['field_product_image']['target_id'] = '18';
    $variation = [
      'id' => 14,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-095',
      'price' => '299.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(14, $attributes, $files);

    $attributes['attribute_color']['id'] = '27';
    $attributes['attribute_color']['value'] = 'Purple';
    $attributes['attribute_size']['id'] = '30';
    $attributes['attribute_size']['value'] = '10';
    $files['field_product_image']['target_id'] = '19';
    $variation = [
      'id' => 15,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'HE-102',
      'price' => '349.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(15, $attributes, $files);

    $attributes = [];
    $files['field_product_image'] =
      [
        'target_id' => '20',
        'alt' => '',
        'title' => '',
        'width' => '225',
        'height' => '225',
      ];
    $files['field_product_image_2'] =
      [
        'target_id' => '2',
        'alt' => '',
        'title' => '',
        'width' => '211',
        'height' => '239',
      ];
    $variation = [
      'id' => 16,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-01',
      'price' => '349.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(16, $attributes, $files);

    $files['field_product_image'] =
      [
        'target_id' => '22',
        'alt' => '',
        'title' => '',
        'width' => '322',
        'height' => '156',
      ];
    $files['field_product_image_2'] = [];
    $variation = [
      'id' => 17,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-03',
      'price' => '25.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(17, $attributes, $files);

    $files['field_product_image'] =
      [
        'target_id' => '24',
        'alt' => '',
        'title' => '',
        'width' => '225',
        'height' => '225',
      ];
    $variation = [
      'id' => 18,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-04',
      'price' => '14.990000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(18, $attributes, $files);

    $files['field_product_image'] =
      [
        'target_id' => '25',
        'alt' => '',
        'title' => '',
        'width' => '322',
        'height' => '156',
      ];
    $variation = [
      'id' => 19,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-05',
      'price' => '9.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(19, $attributes, $files);

    $files['field_product_image']['target_id'] = '26';
    $variation = [
      'id' => 20,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-06',
      'price' => '10.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(20, $attributes, $files);

    $files['field_product_image']['target_id'] = '27';
    $variation = [
      'id' => 21,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-07',
      'price' => '10.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(21, $attributes, $files);

    $files['field_product_image'] =
      [
        'target_id' => '28',
        'alt' => '',
        'title' => '',
        'width' => '225',
        'height' => '225',
      ];
    $variation = [
      'id' => 22,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-08',
      'price' => '6.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(22, $attributes, $files);

    $files['field_product_image'] =
      [
        'target_id' => '29',
        'alt' => '',
        'title' => '',
        'width' => '322',
        'height' => '156',
      ];
    $variation = [
      'id' => 23,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-09',
      'price' => '7.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(23, $attributes, $files);

    $files['field_product_image'] =
      [
        'target_id' => '30',
        'alt' => '',
        'title' => '',
        'width' => '225',
        'height' => '225',
      ];
    $variation = [
      'id' => 24,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-10',
      'price' => '11.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(24, $attributes, $files);

    $files['field_product_image']['target_id'] = '31';
    $variation = [
      'id' => 25,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-12',
      'price' => '6.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(25, $attributes, $files);

    $files['field_product_image'] =
      [
        'target_id' => '32',
        'alt' => '',
        'title' => '',
        'width' => '322',
        'height' => '156',
      ];
    $variation = [
      'id' => 26,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'MC-13',
      'price' => '14.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(26, $attributes, $files);

    $files['field_product_image']['target_id'] = '33';
    $variation = [
      'id' => 27,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'GO-01',
      'price' => '5.950000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(27, $attributes, $files);

    $files['field_product_image']['target_id'] = '34';
    $files['field_product_image_3'] =
      [
        'target_id' => '35',
        'alt' => '',
        'title' => '',
        'width' => '88',
        'height' => '100',
      ];

    $variation = [
      'id' => 28,
      'type' => 'default',
      'uid' => '0',
      'sku' => 'GO-50',
      'price' => '399.000000',
      'currency' => 'CAD',
      'product_id' => NULL,
      'title' => '',
      'order_item_type_id' => 'default',
      'created_time' => NULL,
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);
    $this->assertProductVariationEntityAdditions(28, $attributes, $files);
  }

  /**
   * Asserts additions to a product variation.
   *
   * @param int $id
   *   The product variation id.
   * @param array $attributes
   *   Array of attribute names and id.
   * @param array $files
   *   Array of file information.
   */
  public function assertProductVariationEntityAdditions($id, array $attributes, array $files) {
    $variation = ProductVariation::load($id);
    foreach ($attributes as $name => $data) {
      if ($data) {
        $this->assertSame($data['id'], $variation->getAttributeValueId($name));
        $this->assertSame($data['value'], $variation->getAttributeValue($name)
          ->getName());
      }
    }
    foreach ($files as $name => $data) {
      if ($data) {
        $this->assertSame([$data], $variation->get($name)
          ->getValue(), "File data for $name is incorrect.");
      }
      else {
        $this->assertSame($data, $variation->get($name)->getValue());
      }
    }
  }

}
