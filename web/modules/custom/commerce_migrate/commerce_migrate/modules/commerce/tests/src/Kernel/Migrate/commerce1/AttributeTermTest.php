<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests attribute value migration.
 *
 * @requires module migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class AttributeTermTest extends Commerce1TestBase {

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
    'image',
    'link',
    'menu_ui',
    'migrate_plus',
    'node',
    'path',
    'profile',
    'taxonomy',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('profile');
    // Setup files needed for the taxonomy_term:collection migration.
    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('file');
    $this->container->get('stream_wrapper_manager')->registerWrapper('public', PublicStream::class, StreamWrapperInterface::NORMAL);
    $fs = \Drupal::service('file_system');
    // The public file directory active during the test will serve as the
    // root of the fictional Drupal 7 site we're migrating.
    $fs->mkdir('public://sites/default/files', NULL, TRUE);

    $file_paths = [
      'collection-banner-to_wear.jpg',
      'collection-banner-to_carry.jpg',
      'collection-banner-to_drink_with.jpg',
      'collection-banner-to_geek_out.jpg',
    ];
    foreach ($file_paths as $file_path) {
      $filename = 'public://sites/default/files/' . $file_path;
      file_put_contents($filename, str_repeat('*', 8));
    }
    /** @var \Drupal\migrate\Plugin\Migration $migration */
    $migration = $this->getMigration('d7_file');
    // Set the source plugin's source_base_path configuration value, which
    // would normally be set by the user running the migration.
    $source = $migration->getSourceConfiguration();
    $source['constants']['source_base_path'] = $fs->realpath('public://');
    $migration->set('source', $source);
    $this->executeMigration($migration);

    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('commerce_product_attribute_value');

    $this->migrateFields();
    $this->executeMigrations([
      'd7_taxonomy_term',
    ]);
  }

  /**
   * Test attribute migrations from Commerce 1.
   */
  public function testMigrateProductAttributeValueTest() {
    $this->assertProductAttributeValueEntity('1', 'bag_size', 'One Size', 'One Size', '0');
    $this->assertProductAttributeValueEntity('2', 'bag_size', '13"', '13"', '0');
    $this->assertProductAttributeValueEntity('3', 'bag_size', '15"', '15"', '0');
    $this->assertProductAttributeValueEntity('4', 'bag_size', '17"', '17"', '0');

    $this->assertProductAttributeValueEntity('5', 'color', 'Green', 'Green', '0');
    $this->assertProductAttributeValueEntity('6', 'color', 'Blue', 'Blue', '0');
    $this->assertProductAttributeValueEntity('7', 'color', 'Black', 'Black', '0');
    $this->assertProductAttributeValueEntity('8', 'color', 'Yellow', 'Yellow', '0');
    $this->assertProductAttributeValueEntity('9', 'color', 'Silver', 'Silver', '0');
    $this->assertProductAttributeValueEntity('10', 'color', 'Gray', 'Gray', '0');
    $this->assertProductAttributeValueEntity('11', 'color', 'Red', 'Red', '0');
    $this->assertProductAttributeValueEntity('12', 'color', 'Purple', 'Purple', '0');
    $this->assertProductAttributeValueEntity('13', 'color', 'Cream', 'Cream', '0');
    $this->assertProductAttributeValueEntity('14', 'color', 'Light Blue', 'Light Blue', '0');
    $this->assertProductAttributeValueEntity('15', 'color', 'Orange', 'Orange', '0');
    $this->assertProductAttributeValueEntity('16', 'color', 'Fuchia', 'Fuchia', '0');
    $this->assertProductAttributeValueEntity('17', 'color', 'Pink', 'Pink', '0');

    $this->assertProductAttributeValueEntity('18', 'hat_size', 'One Size', 'One Size', '0');

    $this->assertProductAttributeValueEntity('19', 'shoe_size', 'Mens 4/5 (Womens 5/6)', 'Mens 4/5 (Womens 5/6)', '0');
    $this->assertProductAttributeValueEntity('20', 'shoe_size', 'Mens 6 (Womens 7/8)', 'Mens 6 (Womens 7/8)', '0');
    $this->assertProductAttributeValueEntity('21', 'shoe_size', 'Mens 7/8 (Womens 9/10)', 'Mens 7/8 (Womens 9/10)', '0');
    $this->assertProductAttributeValueEntity('22', 'shoe_size', 'Mens 9 (Womens 11/12)', 'Mens 9 (Womens 11/12)', '0');
    $this->assertProductAttributeValueEntity('23', 'shoe_size', 'Mens 10/11', 'Mens 10/11', '0');
    $this->assertProductAttributeValueEntity('24', 'shoe_size', 'Mens 12', 'Mens 12', '0');
    $this->assertProductAttributeValueEntity('25', 'shoe_size', 'Mens 4 (Womens 6)', 'Mens 4 (Womens 6)', '0');
    $this->assertProductAttributeValueEntity('26', 'shoe_size', 'Mens 5 (Womens 7)', 'Mens 5 (Womens 7)', '0');
    $this->assertProductAttributeValueEntity('27', 'shoe_size', 'Mens 6 (Womens 8)', 'Mens 6 (Womens 8)', '0');
    $this->assertProductAttributeValueEntity('28', 'shoe_size', 'Mens 7 (Womens 9)', 'Mens 7 (Womens 9)', '0');
    $this->assertProductAttributeValueEntity('29', 'shoe_size', 'Mens 8 (Womens 10)', 'Mens 8 (Womens 10)', '0');
    $this->assertProductAttributeValueEntity('30', 'shoe_size', 'Mens 9 (Womens 11)', 'Mens 9 (Womens 11)', '0');
    $this->assertProductAttributeValueEntity('31', 'shoe_size', 'Mens 10 (Womens 12)', 'Mens 10 (Womens 12)', '0');
    $this->assertProductAttributeValueEntity('32', 'shoe_size', 'Mens 11', 'Mens 11', '0');
    $this->assertProductAttributeValueEntity('33', 'shoe_size', 'Mens 12', 'Mens 12', '0');

    $this->assertProductAttributeValueEntity('34', 'storage_capacity_with_very_lon', '8GB', '8GB', '0');
    $this->assertProductAttributeValueEntity('35', 'storage_capacity_with_very_lon', '16GB', '16GB', '1');
    $this->assertProductAttributeValueEntity('36', 'storage_capacity_with_very_lon', '32GB', '32GB', '2');

    $this->assertProductAttributeValueEntity('37', 'top_size', 'Small', 'Small', '0');
    $this->assertProductAttributeValueEntity('38', 'top_size', 'Medium', 'Medium', '0');
    $this->assertProductAttributeValueEntity('39', 'top_size', 'Large', 'Large', '0');
  }

}
