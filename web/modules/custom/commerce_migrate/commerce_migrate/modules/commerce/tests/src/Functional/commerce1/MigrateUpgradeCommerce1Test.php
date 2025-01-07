<?php

namespace Drupal\Tests\commerce_migrate_commerce\Functional\commerce1;

use Composer\Semver\Comparator;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\Tests\commerce_migrate\Functional\MigrateUpgradeTestTrait;
use Drupal\Tests\migrate_drupal_ui\Functional\MigrateUpgradeTestBase;

/**
 * Tests Commerce 1 upgrade using the migrate UI.
 *
 * The test method is provided by the MigrateUpgradeTestBase class.
 *
 * @requires module migrate_plus
 * @requires module commerce_shipping
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class MigrateUpgradeCommerce1Test extends MigrateUpgradeTestBase {

  use MigrateUpgradeTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'block',
    'block_content',
    'comment',
    'dblog',
    'field',
    'filter',
    'node',
    'path',
    'path_alias',
    'search',
    'shortcut',
    'system',
    'taxonomy',
    'user',
    'address',
    'commerce',
    'commerce_cart',
    'commerce_log',
    'commerce_order',
    'commerce_payment',
    'commerce_price',
    'commerce_product',
    'commerce_promotion',
    'commerce_store',
    'commerce_migrate',
    'commerce_shipping',
    'commerce_tax',
    'migrate',
    'migrate_drupal',
    'migrate_drupal_ui',
    'address',
    'datetime',
    'entity_reference_revisions',
    'file',
    'image',
    'link',
    'options',
    'telephone',
    'text',
    'entity',
    'profile',
    'inline_entity_form',
    'state_machine',
    'views',
    'migrate_plus',
    'commerce_migrate_commerce',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->writeSettings([
      'settings' => [
        'migrate_node_migrate_type_classic' => (object) [
          'value' => TRUE,
          'required' => TRUE,
        ],
      ],
    ]);
    $this->loadFixture(\Drupal::service('extension.list.module')->getPath('commerce_migrate_commerce') . '/tests/fixtures/ck2.php');
  }

  /**
   * Executes all steps of migrations upgrade.
   */
  public function testMigrateUpgrade() {
    $this->drupalGet('/upgrade');
    $session = $this->assertSession();
    $session->responseContains("Upgrade a site by importing its files and the data from its database into a clean and empty new install of Drupal $this->destinationSiteVersion.");

    $this->submitForm([], 'Continue');
    $session->pageTextContains('Provide credentials for the database of the Drupal site you want to upgrade.');

    // Get valid credentials.
    $edits = $this->translatePostValues($this->getCredentials());

    $this->submitForm($edits, 'Review upgrade');
    $session->statusCodeEquals(200);

    $this->submitForm([], 'Perform upgrade');
    $this->assertUpgrade($this->getEntityCounts());

    $plugin_manager = \Drupal::service('plugin.manager.migration');
    /** @var \Drupal\migrate\Plugin\Migration[] $all_migrations */
    $all_migrations = $plugin_manager->createInstancesByTag('Drupal 7');
    foreach ($all_migrations as $migration) {
      // Prior to Drupal 9.4.0-alpha1 the d7_action migration did not run in
      // this test. It does now because the action migrations were moved to the
      // system module. See https://www.drupal.org/node/3110401
      // @todo https://www.drupal.org/project/commerce_migrate/issues/3295947
      if ($migration->getBaseId() === 'd7_action') {
        continue;
      }
      $id_map = $migration->getIdMap();
      foreach ($id_map as $source_id => $map) {
        // Convert $source_id into a keyless array so that
        // \Drupal\migrate\Plugin\migrate\id_map\Sql::getSourceHash() works as
        // expected.
        $source_id_values = array_values(unserialize($source_id));
        $row = $id_map->getRowBySource($source_id_values);
        $destination = serialize($id_map->currentDestination());
        $message = "Migration of $source_id to $destination as part of the {$migration->id()} migration. The source row status is " . $row['source_row_status'];
        // A completed migration should have maps with
        // MigrateIdMapInterface::STATUS_IGNORED or
        // MigrateIdMapInterface::STATUS_IMPORTED.
        if ($row['source_row_status'] == MigrateIdMapInterface::STATUS_FAILED || $row['source_row_status'] == MigrateIdMapInterface::STATUS_NEEDS_UPDATE) {
          $this->fail($message);
        }
        else {
          self::assertNotEmpty($message);
        }
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceBasePath() {
    return __DIR__ . '/files';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCounts() {
    $entity_counts = [
      'block' => 26,
      'comment_type' => 11,
      'commerce_log' => 18,
      'commerce_number_pattern' => 1,
      'commerce_order' => 5,
      'commerce_order_type' => 1,
      'commerce_order_item' => 14,
      'commerce_order_item_type' => 3,
      'commerce_package_type' => 0,
      'commerce_payment_gateway' => 1,
      'commerce_payment_method' => 0,
      'commerce_payment' => 3,
      'commerce_currency' => 1,
      'commerce_product_variation' => 84,
      'commerce_product' => 20,
      'commerce_product_type' => 7,
      'commerce_product_variation_type' => 8,
      'commerce_product_attribute' => 6,
      'commerce_product_attribute_value' => 39,
      'commerce_promotion_coupon' => 0,
      'commerce_promotion' => 0,
      'commerce_shipping_method' => 3,
      'commerce_shipment' => 0,
      'commerce_shipment_type' => 1,
      'commerce_store' => 1,
      'commerce_store_type' => 1,
      'commerce_tax_type' => 1,
      'contact_form' => 2,
      'contact_message' => 0,
      'field_storage_config' => 42,
      'field_config' => 113,
      'file' => 105,
      'filter_format' => 6,
      'migration_group' => 1,
      'migration' => 0,
      'node' => 17,
      'node_type' => 11,
      'path_alias' => 121,
      'profile' => 13,
      'profile_type' => 3,
      'search_page' => 3,
      'action' => 29,
      'menu' => 10,
      'taxonomy_term' => 33,
      'taxonomy_vocabulary' => 12,
      'user' => 3,
      'user_role' => 4,
      'menu_link_content' => 0,
      'base_field_override' => 1,
      'entity_form_display' => 48,
      'entity_view_mode' => 45,
      'entity_form_mode' => 5,
      'entity_view_display' => 153,
    ];
    if (Comparator::greaterThanOrEqualTo(\Drupal::VERSION, '9.4.0-alpha1')) {
      $entity_counts['block'] = 29;
      $entity_counts['date_format'] = 12;
    }
    return $entity_counts;
  }

  /**
   * {@inheritdoc}
   */
  protected function getAvailablePaths() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMissingPaths() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCountsIncremental() {}

}
