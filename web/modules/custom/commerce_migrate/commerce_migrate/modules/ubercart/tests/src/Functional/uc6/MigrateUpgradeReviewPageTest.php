<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Functional\uc6;

use Drupal\Tests\commerce_migrate\Functional\MigrateUpgradeTestTrait;
use Drupal\Tests\migrate_drupal_ui\Functional\MultilingualReviewPageTestBase;

/**
 * Tests migrate upgrade review page for Ubercart 6.
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class MigrateUpgradeReviewPageTest extends MultilingualReviewPageTestBase {

  use MigrateUpgradeTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'action',
    'address',
    'commerce',
    'commerce_price',
    'commerce_store',
    'commerce_order',
    'commerce_product',
    'commerce_migrate',
    'commerce_migrate_ubercart',
    'commerce_shipping',
    'content_translation',
    'entity',
    'entity_reference_revisions',
    'inline_entity_form',
    'language',
    'locale',
    'path',
    'path_alias',
    'profile',
    'physical',
    'state_machine',
    'text',
    'views',
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
  protected function getAvailablePaths() {
    return [
      'Flatrate',
      'Order',
      'Product',
      'Product attributes',
      'Store',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMissingPaths() {
    return [
      '2Checkout',
      'Authorize.net',
      'Cart',
      'Cart Links',
      'Catalog',
      'Conditional Actions',
      'Credit card',
      'CyberSource',
      'File downloads',
      'Google Analytics for Ubercart',
      'Google Checkout',
      'PayPal',
      'Payment',
      'Payment method pack',
      'Product Kit',
      'Profile translation',
      'Reports',
      'Roles',
      'Shipping',
      'Shipping Quotes',
      'Stock',
      'Tax report',
      'Taxes',
      'U.S. Postal Service',
      'UPS',
      'Update status',
      'Weight quote',
    ];
  }

}
