<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\Core\Database\Database;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;
use Drupal\Tests\migrate\Kernel\MigrateDumpAlterInterface;

/**
 * Tests product type migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class ProductTypeTest extends Commerce1TestBase implements MigrateDumpAlterInterface {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'path',
    'commerce_price',
    'commerce_product',
    'commerce_store',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('commerce_product');
    $migration = $this->getMigration('commerce1_product_type');
    $this->executeMigration($migration);

    // Rerun the migration.
    $table_name = $migration->getIdMap()->mapTableName();
    $default_connection = \Drupal::database();
    $default_connection->truncate($table_name)->execute();
    $this->executeMigration($migration);
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
      'hats' => 'hats',
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
   * Test product type migration from Drupal 7 to 8.
   */
  public function testProductType() {
    $type = [
      'id' => 'bags_cases',
      'label' => 'Bags & Cases',
      'description' => 'A <em>Bags & Cases</em> is a content type which contain product variations.',
      'variation_type' => 'bags_cases',
    ];
    $this->assertProductTypeEntity($type['id'], $type['label'], $type['description'], $type['variation_type']);
    $type = [
      'id' => 'drinks',
      'label' => 'Drinks',
      'description' => 'A <em>Drinks</em> is a content type which contain product variations.',
      'variation_type' => 'drinks',
    ];
    $this->assertProductTypeEntity($type['id'], $type['label'], $type['description'], $type['variation_type']);
    $type = [
      'id' => 'hats',
      'label' => 'Hats',
      'description' => 'A <em>Hats</em> is a content type which contain product variations.',
      'variation_type' => 'hats',
    ];
    $this->assertProductTypeEntity($type['id'], $type['label'], $type['description'], $type['variation_type']);
    $type = [
      'id' => 'shoes',
      'label' => 'Shoes',
      'description' => 'A <em>Shoes</em> is a content type which contain product variations.',
      'variation_type' => 'shoes',
    ];
    $this->assertProductTypeEntity($type['id'], $type['label'], $type['description'], $type['variation_type']);
    $type = [
      'id' => 'storage_devices',
      'label' => 'Storage Devices',
      'description' => 'A <em>Storage Devices</em> is a content type which contain product variations.',
      'variation_type' => 'storage_devices',
    ];
    $this->assertProductTypeEntity($type['id'], $type['label'], $type['description'], $type['variation_type']);
    $type = [
      'id' => 'tops',
      'label' => 'Tops',
      'description' => 'A <em>Tops</em> is a content type which contain product variations.',
      'variation_type' => 'tops',
    ];
    $this->assertProductTypeEntity($type['id'], $type['label'], $type['description'], $type['variation_type']);
  }

}
