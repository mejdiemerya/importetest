<?php

namespace Drupal\Tests\commerce_migrate_commerce\Functional\commerce1;

use Composer\Semver\Comparator;
use Drupal\Tests\commerce_migrate\Functional\MigrateUpgradeReviewPageTestBase;
use Drupal\Tests\commerce_migrate\Functional\MigrateUpgradeTestTrait;

/**
 * Tests migrate upgrade review page for Commerce 1.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class MigrateUpgradeReviewPageTest extends MigrateUpgradeReviewPageTestBase {

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
    'commerce_log',
    'commerce_migrate',
    'commerce_migrate_commerce',
    'commerce_order',
    'commerce_payment',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'entity',
    'entity_reference_revisions',
    'inline_entity_form',
    'path',
    'path_alias',
    'profile',
    'state_machine',
    'text',
    'views',
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
   * {@inheritdoc}
   */
  protected function getSourceBasePath() {
    return __DIR__ . '/files';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAvailablePaths() {
    $available_paths = [
      'Commerce',
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
      'Commerce Addressbook',
      'Commerce American Express',
      'Commerce AutoSKU',
      'Commerce Backoffice',
      'Commerce Backoffice Order',
      'Commerce Backoffice Product',
      'Commerce Backoffice content',
      'Commerce Checkout Progress',
      'Commerce Checkout Redirect',
      'Commerce Checkout by Amazon',
      'Commerce Customer Dummy Profile',
      'Commerce Discount',
      'Commerce Extra Price Formatters',
      'Commerce Features',
      'Commerce Kickstart',
      'Commerce Kickstart Block',
      'Commerce Kickstart Blog',
      'Commerce Kickstart Comment',
      'Commerce Kickstart Help',
      'Commerce Kickstart Inline Help',
      'Commerce Kickstart Lite Product',
      'Commerce Kickstart Menus',
      'Commerce Kickstart Merchandising',
      'Commerce Kickstart Migrate',
      'Commerce Kickstart Order',
      'Commerce Kickstart Product',
      'Commerce Kickstart Product UI',
      'Commerce Kickstart Reset',
      'Commerce Kickstart Search',
      'Commerce Kickstart Slideshow',
      'Commerce Kickstart Social',
      'Commerce Kickstart Taxonomy',
      'Commerce Kickstart User',
      'Commerce Login and Pay with Amazon',
      'Commerce Migrate',
      'Commerce Moneybookers',
      'Commerce Moneybookers Quick Checkout',
      'Commerce PayLeap',
      'Commerce Paymill',
      'Commerce Search API',
      'Commerce UI',
      'Commerce Yotpo',
      'Commerce add to cart confirmation',
      'Commerce fancy attributes',
      'Commerce payment offsite test',
    ];
    if (Comparator::greaterThanOrEqualTo(\Drupal::VERSION, '9.4.0-alpha1')) {
      $missing_paths[] = 'Color';
    }
    return $missing_paths;
  }

}
