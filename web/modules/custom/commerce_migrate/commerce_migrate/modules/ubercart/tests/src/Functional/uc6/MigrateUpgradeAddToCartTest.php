<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Functional\uc6;

use Drupal\Tests\migrate_drupal_ui\Functional\MigrateUpgradeTestBase;

/**
 * Tests adding a product to the cart after a full migration.
 *
 * @requires module migrate_plus
 * @requires module commerce_shipping
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class MigrateUpgradeAddToCartTest extends MigrateUpgradeTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'address',
    'block',
    'block_content',
    'comment',
    'commerce',
    'commerce_cart',
    'commerce_log',
    'commerce_migrate',
    'commerce_migrate_ubercart',
    'commerce_number_pattern',
    'commerce_order',
    'commerce_payment',
    'commerce_price',
    'commerce_product',
    'commerce_promotion',
    'commerce_shipping',
    'commerce_store',
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
    $this->writeSettings([
      'settings' => [
        'migrate_node_migrate_type_classic' => (object) [
          'value' => TRUE,
          'required' => TRUE,
        ],
      ],
    ]);
    $this->loadFixture(\Drupal::service('extension.list.module')->getPath('commerce_migrate_ubercart') . '/tests/fixtures/uc6.php');
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

    // Prevent error that the new field, field_order_admin_comments does not
    // exist on the entity type commerce_order.
    drupal_flush_all_caches();

    // Add to cart.
    \Drupal::service('commerce_cart.cart_provider')->createCart('default');
    $this->drupalGet('/product/2');
    $this->submitForm([], 'Add to cart');
    $session->pageTextContains('Beach Towel added to your cart.');
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
    return [];
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
