<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Functional\uc6;

use Composer\Semver\Comparator;
use Drupal\Tests\commerce_migrate_ubercart\Functional\MigrateUpgradeExecuteTestBase;

/**
 * Tests Ubercart 6 migration using the Migrate Drupal UI.
 *
 * @requires module migrate_plus
 * @requires module commerce_shipping
 * @requires module physical
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class MigrateUpgradeUbercart6Test extends MigrateUpgradeExecuteTestBase {

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
    'commerce_order',
    'commerce_payment',
    'commerce_price',
    'commerce_product',
    'commerce_promotion',
    'commerce_shipping',
    'commerce_store',
    'commerce_tax',
    'commerce_migrate',
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
    'physical',
    'profile',
    'inline_entity_form',
    'state_machine',
    'views',
    'migrate_plus',
    'commerce_migrate_ubercart',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->loadFixture(\Drupal::service('extension.list.module')->getPath('commerce_migrate_ubercart') . '/tests/fixtures/uc6.php');
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceBasePath() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCounts() {
    $entity_counts = [
      'action' => 33,
      'base_field_override' => 6,
      'block' => 23,
      'comment_type' => 6,
      'commerce_currency' => 2,
      'commerce_number_pattern' => 1,
      'commerce_order' => 5,
      'commerce_order_item' => 6,
      'commerce_order_item_type' => 1,
      'commerce_order_type' => 1,
      'commerce_package_type' => 0,
      'commerce_payment' => 4,
      'commerce_payment_gateway' => 2,
      'commerce_payment_method' => 0,
      'commerce_product' => 5,
      'commerce_product_attribute' => 4,
      'commerce_product_attribute_value' => 7,
      'commerce_product_type' => 4,
      'commerce_product_variation' => 5,
      'commerce_product_variation_type' => 4,
      'commerce_promotion' => 0,
      'commerce_promotion_coupon' => 0,
      'commerce_shipment' => 0,
      'commerce_shipment_type' => 1,
      'commerce_shipping_method' => 2,
      'commerce_store' => 1,
      'commerce_store_type' => 1,
      'commerce_tax_type' => 1,
      'entity_form_display' => 22,
      'entity_form_mode' => 5,
      'entity_view_display' => 35,
      'entity_view_mode' => 30,
      'field_config' => 39,
      'field_storage_config' => 31,
      'filter_format' => 5,
      'menu' => 8,
      'migration' => 0,
      'migration_group' => 1,
      'node' => 2,
      'node_type' => 3,
      'path_alias' => 2,
      'profile' => 4,
      'profile_type' => 1,
      'search_page' => 3,
      'taxonomy_term' => 3,
      'taxonomy_vocabulary' => 2,
      'user' => 6,
      'user_role' => 6,
    ];
    if (Comparator::greaterThanOrEqualTo(\Drupal::VERSION, '9.4.0-alpha1')) {
      $entity_counts['block'] = 26;
    }
    return $entity_counts;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCountsIncremental() {}

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

}
