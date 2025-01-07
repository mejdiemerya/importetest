<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Database\Database;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;
use Drupal\Tests\migrate\Kernel\MigrateDumpAlterInterface;

/**
 * Tests product migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class ProductTest extends Commerce1TestBase implements MigrateDumpAlterInterface {

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
      'commerce1_product_attribute',
      'd7_taxonomy_term',
    ]);
    $this->migrateProducts();
  }

  /**
   * {@inheritdoc}
   */
  public static function migrateDumpAlter(KernelTestBase $test) {
    $db = Database::getConnection('default', 'migrate');

    // Remove all product types that can be referenced by the hat content type.
    $results = $db->select('field_config_instance', 'fci')
      ->condition('field_name', 'field_product')
      ->condition('entity_type', 'node')
      ->condition('bundle', 'hats')
      ->fields('fci', ['data'])
      ->execute()
      ->fetchCol();

    if ($results) {
      $data = unserialize(reset($results));
    }
    $data['settings']['referenceable_types'] = [
      'bags_cases' => 0,
      'drinks' => 0,
      'hats' => 0,
      'shoes' => 0,
      'storage_devices' => 0,
      'tops' => 0,
    ];

    $data = serialize($data);
    $db->update('field_config_instance')
      ->condition('field_name', 'field_product')
      ->condition('entity_type', 'node')
      ->condition('bundle', 'hats')
      ->fields(['data' => $data])
      ->execute();

    // Change the  product types that can be referenced by the shoe content
    // type to 'hats' and 'shoes'.
    $results = $db->select('field_config_instance', 'fci')
      ->condition('field_name', 'field_product')
      ->condition('entity_type', 'node')
      ->condition('bundle', 'shoes')
      ->fields('fci', ['data'])
      ->execute()
      ->fetchCol();

    if ($results) {
      $data = unserialize(reset($results));
    }
    $data['settings']['referenceable_types'] = [
      'bags_cases' => 0,
      'drinks' => 0,
      'hats' => 'shoes',
      'shoes' => 'shoes',
      'storage_devices' => 0,
      'tops' => 0,
    ];

    $data = serialize($data);
    $db->update('field_config_instance')
      ->condition('field_name', 'field_product')
      ->condition('entity_type', 'node')
      ->condition('bundle', 'shoes')
      ->fields(['data' => $data])
      ->execute();
  }

  /**
   * Test product migration from Drupal 7 to 8.
   */
  public function testProduct() {
    $this->assertProductEntity(
      15,
      'bags_cases',
      '1',
      'Go green with Drupal Commerce Reusable Tote Bag',
      TRUE,
      ['1'],
      ['1']
    );
    $this->assertProductEntity(
      22,
      'hats',
      '1',
      'Commerce Guys Baseball Cap',
      TRUE,
      ['1'],
      ['1']
    );
    $this->assertProductEntity(
      23,
      'hats',
      '1',
      'Drupal Commerce Ski Cap',
      TRUE,
      ['1'],
      ['1']
    );
    // Tests a product with multiple variations.
    $this->assertProductEntity(
      26,
      'storage_devices',
      '1',
      'Commerce Guys USB Key',
      TRUE,
      ['1'],
      [
        '28',
        '29',
        '30',
      ]
    );

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

    // Test values of a product field.
    $product = Product::load(15);
    $this->assertInstanceOf(Product::class, $product);
    $this->assertCount(1, $product->get('field_category')->getValue());
    $this->assertSame('50', $product->get('field_category')
      ->getValue()[0]['target_id']);
  }

}
