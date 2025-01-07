<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Tests\commerce_cart\Traits\CartManagerTestTrait;
use Drupal\Tests\migrate_drupal\Kernel\d7\MigrateDrupal7TestBase;

/**
 * Base class for Commerce 1 migration tests.
 */
abstract class Commerce1TestBase extends MigrateDrupal7TestBase {

  use CartManagerTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'action',
    'address',
    'commerce',
    'entity',
    'entity_reference_revisions',
    'inline_entity_form',
    'profile',
    'state_machine',
    'text',
    'views',
    // Commerce migrate requirements.
    'commerce_migrate',
    'commerce_migrate_commerce',
  ];

  /**
   * Gets the path to the fixture file.
   */
  protected function getFixtureFilePath() {
    return __DIR__ . '/../../../../fixtures/ck2.php';
  }

  /**
   * Executes field migration.
   *
   * Required modules:
   * - comment.
   * - datetime.
   * - field.
   * - image.
   * - link.
   * - menu_ui.
   * - node.
   * - profile.
   * - taxonomy.
   * - telephone.
   * - text.
   */
  protected function migrateFields() {
    $this->installEntitySchema('commerce_store');
    $this->migrateContentTypes();
    $this->migrateCommentTypes();
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('profile');
    $this->executeMigration('d7_field');
    $this->executeMigrations(['d7_taxonomy_vocabulary', 'd7_field_instance']);
  }

  /**
   * Executes content type migration.
   *
   * Required modules:
   * - commerce_product.
   * - node.
   */
  protected function migrateContentTypes() {
    parent::migrateContentTypes();
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->executeMigrations([
      'commerce1_product_variation_type',
      'commerce1_product_type',
    ]);
  }

  /**
   * Executes order migration.
   *
   * Required modules:
   * - commerce_order.
   * - commerce_price.
   * - commerce_product.
   * - commerce_store.
   * - migrate_plus.
   * - path.
   */
  protected function migrateOrders() {
    $this->migrateOrderItems();
    $this->migrateStore();
    $this->migrateProfiles();
    $this->executeMigrations([
      'commerce1_product_variation_type',
      'commerce1_product_variation',
      'commerce1_order_item_type',
      'commerce1_order_item',
      'commerce1_order',
    ]);
  }

  /**
   * Executes order migration with the cart enabled.
   *
   * Required modules:
   * - commerce_order.
   * - commerce_price.
   * - commerce_product.
   * - commerce_store.
   * - migrate_plus.
   * - path.
   */
  protected function migrateOrdersWithCart() {
    $this->migrateOrderItems();
    $this->migrateStore();
    // Installing the cart requires that the store has a country code.
    /** @var \Drupal\commerce_store\Entity\Store $store */
    $store = \Drupal::entityTypeManager()->getStorage('commerce_store')->load(1);
    $address = $store->getAddress();
    $address->country_code = 'NZ';
    $address->address_line1 = '123 Nowhere St';
    $address->locality = 'Wellington';
    $store->setAddress($address);
    $store->save();
    $this->installCommerceCart();

    $this->migrateProfiles();
    $this->executeMigrations([
      'commerce1_product_variation_type',
      'commerce1_product_variation',
      'commerce1_order_item_type',
      'commerce1_order_item',
      'commerce1_order',
    ]);
  }

  /**
   * Executes order item migration.
   *
   * Required modules:
   * - commerce_order.
   * - commerce_price.
   * - commerce_product.
   * - commerce_store.
   * - migrate_plus.
   * - path.
   */
  protected function migrateOrderItems() {
    $this->installEntitySchema('commerce_store');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('profile');
    $this->installConfig(['commerce_order']);
    $this->migrateProducts();
    $this->installSchema('commerce_number_pattern', ['commerce_number_pattern_sequence']);
    $this->installConfig(['commerce_order', 'commerce_product']);
    $this->executeMigrations([
      'commerce1_order_item_type',
      'commerce1_order_item',
    ]);
  }

  /**
   * Executes product migration.
   *
   * Required modules:
   * - commerce_price.
   * - commerce_product.
   * - commerce_store.
   * - path.
   */
  protected function migrateProducts() {
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('path_alias');
    $this->migrateStore();
    $this->migrateProductVariations();
    $this->executeMigrations([
      'commerce1_product_type',
      'commerce1_product',
    ]);
  }

  /**
   * Executes product variation migration.
   *
   * Required modules:
   * - commerce_price.
   * - commerce_product.
   * - commerce_store.
   * - path.
   */
  protected function migrateProductVariations() {
    $this->installEntitySchema('view');
    $this->installEntitySchema('commerce_product_variation');
    $this->executeMigrations([
      'commerce1_product_variation_type',
      'commerce1_product_variation',
    ]);
  }

  /**
   * Executes profile migrations.
   *
   * Required modules:
   * - commerce_order.
   * - commerce_price.
   * - commerce_product.
   * - commerce_store.
   * - migrate_plu.
   * - path.
   */
  protected function migrateProfiles() {
    $this->installEntitySchema('commerce_number_pattern');
    $this->installEntitySchema('commerce_store');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_product');
    $this->installEntitySchema('commerce_product_variation');
    $this->installEntitySchema('profile');
    $this->installEntitySchema('view');
    $this->installConfig('commerce_order');
    $this->installConfig('commerce_product');
    $this->installConfig('profile');
    $this->migrateUsers(FALSE);
    $this->executeMigrations([
      'commerce1_profile_type',
      'commerce1_profile',
      'commerce1_profile_revision',
    ]);
  }

  /**
   * Executes store migration.
   *
   * Required modules:
   * - commerce_currency.
   * - commerce_store.
   */
  protected function migrateStore() {
    $this->installEntitySchema('commerce_store');
    $this->migrateUsers(FALSE);
    $this->executeMigrations([
      'commerce1_currency',
      'commerce1_store',
    ]);
  }

  /**
   * Creates files for the node and product variation migration tests.
   */
  protected function migrateFiles() {
    // Setup files needed.
    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('file');
    $this->container->get('stream_wrapper_manager')
      ->registerWrapper('public', PublicStream::class, StreamWrapperInterface::NORMAL);
    $fs = \Drupal::service('file_system');
    // The public file directory active during the test will serve as the
    // root of the fictional Drupal 7 site we're migrating.
    $fs->mkdir('public://sites/default/files/', NULL, TRUE);

    $files = $this->sourceDatabase->select('file_managed', 'f')
      ->fields('f')
      ->execute()
      ->fetchAll();

    foreach ($files as $file) {
      $sub_dir = str_replace(['public://', $file->filename], '', $file->uri);
      if ($sub_dir) {
        @$fs->mkdir('public://sites/default/files/' . $sub_dir, NULL, TRUE);
      }
      $filepath = str_replace('public://', 'public://sites/default/files/', $file->uri);
      file_put_contents($filepath, str_repeat('*', 8));
    }
    /** @var \Drupal\migrate\Plugin\Migration $migration */
    $migration = $this->getMigration('d7_file');
    // Set the source plugin's source_base_path configuration value, which
    // would normally be set by the user running the migration.
    $source = $migration->getSourceConfiguration();
    $source['constants']['source_base_path'] = $fs->realpath('public://');
    $migration->set('source', $source);
    $this->executeMigration($migration);
  }

}
