<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Functional\uc7;

use Composer\Semver\Comparator;
use Drupal\Tests\commerce_migrate_ubercart\Functional\MigrateUpgradeExecuteTestBase;

/**
 * Tests Ubercart 7 migration using the Migrate Drupal UI.
 *
 * @requires module migrate_plus
 * @requires module commerce_shipping
 * @requires module physical
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
 */
class MigrateUpgradeUbercart7Test extends MigrateUpgradeExecuteTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'address',
    'block',
    'block_content',
    'comment',
    'commerce',
    'commerce_cart',
    'commerce_migrate',
    'commerce_migrate_ubercart',
    'commerce_order',
    'commerce_payment',
    'commerce_price',
    'commerce_product',
    'commerce_promotion',
    'commerce_shipping',
    'commerce_store',
    'commerce_tax',
    'datetime',
    'dblog',
    'entity',
    'entity_reference_revisions',
    'field',
    'file',
    'filter',
    'image',
    'inline_entity_form',
    'link',
    'migrate',
    'migrate_drupal',
    'migrate_drupal_ui',
    'migrate_plus',
    'node',
    'options',
    'path',
    'path_alias',
    'physical',
    'profile',
    'search',
    'shortcut',
    'state_machine',
    'system',
    'taxonomy',
    'telephone',
    'text',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->loadFixture(\Drupal::service('extension.list.module')->getPath('commerce_migrate_ubercart') . '/tests/fixtures/uc7.php');
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
      'action' => 29,
      'base_field_override' => 1,
      'block' => 28,
      'comment_type' => 5,
      'commerce_currency' => 1,
      'commerce_number_pattern' => 1,
      'commerce_order' => 5,
      'commerce_order_item' => 7,
      'commerce_order_item_type' => 1,
      'commerce_order_type' => 1,
      'commerce_package_type' => 0,
      'commerce_payment' => 4,
      'commerce_payment_gateway' => 1,
      'commerce_payment_method' => 0,
      'commerce_product' => 3,
      'commerce_product_attribute' => 4,
      'commerce_product_attribute_value' => 6,
      'commerce_product_type' => 3,
      'commerce_product_variation' => 3,
      'commerce_product_variation_type' => 3,
      'commerce_promotion' => 0,
      'commerce_promotion_coupon' => 0,
      'commerce_shipment' => 0,
      'commerce_shipment_type' => 1,
      'commerce_shipping_method' => 1,
      'commerce_store' => 1,
      'commerce_store_type' => 1,
      'commerce_tax_type' => 3,
      'entity_form_display' => 20,
      'entity_form_mode' => 5,
      'entity_view_display' => 28,
      'entity_view_mode' => 29,
      'field_config' => 36,
      'field_storage_config' => 31,
      'filter_format' => 5,
      'menu' => 5,
      'menu_link_content' => 2,
      'migration' => 0,
      'migration_group' => 1,
      'node' => 2,
      'node_type' => 2,
      'profile' => 3,
      'profile_type' => 1,
      'search_page' => 3,
      'taxonomy_term' => 2,
      'taxonomy_vocabulary' => 2,
      'user' => 4,
      'user_role' => 4,
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
