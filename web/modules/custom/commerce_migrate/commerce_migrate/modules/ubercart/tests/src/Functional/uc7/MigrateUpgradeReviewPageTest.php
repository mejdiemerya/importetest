<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Functional\uc7;

use Composer\Semver\Comparator;
use Drupal\Tests\commerce_migrate\Functional\MigrateUpgradeTestTrait;
use Drupal\Tests\migrate_drupal_ui\Functional\MultilingualReviewPageTestBase;

/**
 * Tests migrate upgrade review page for Ubercart 7.
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
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
  protected function getAvailablePaths() {
    $available_paths = [
      'Flat rate',
      'Order',
      'Product',
      'Product attributes',
      'Store',
    ];
    if (Comparator::greaterThanOrEqualTo(\Drupal::VERSION, '9.4.0-alpha1')) {
      $available_paths = array_diff($available_paths, ['Color']);
    }
    return $available_paths;
  }

  /**
   * {@inheritdoc}
   */
  protected function getMissingPaths() {
    $missing_paths = [
      '2Checkout',
      'Authorize.net',
      'Cart',
      'Cart Links',
      'Catalog',
      'Credit card',
      'CyberSource',
      'Discount Coupon Extended Workflow',
      'Discount Coupon Purchase',
      'Discount Coupon Recurring Payment Integration',
      'Discount Coupons',
      'File downloads',
      'Google Analytics for Ubercart',
      'Google Checkout',
      'PayPal',
      'Payment',
      'Payment method pack',
      'Product kit',
      'Reports',
      'Roles',
      'Shipping',
      'Shipping quotes',
      'Stock',
      'Tax report',
      'Taxes',
      'U.S. Postal Service',
      'UPS',
      'Ubercart Ajax Administration',
      'Weight quote',
    ];
    if (Comparator::greaterThanOrEqualTo(\Drupal::VERSION, '9.4.0-alpha1')) {
      $missing_paths[] = 'Color';
    }
    return $missing_paths;
  }

}
