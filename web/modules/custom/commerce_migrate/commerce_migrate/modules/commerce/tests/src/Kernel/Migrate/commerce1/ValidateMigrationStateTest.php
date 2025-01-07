<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\Tests\migrate_drupal\Traits\ValidateMigrationStateTestTrait;

/**
 * Tests the migration state information in commerce_migrate_ubercart.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class ValidateMigrationStateTest extends Commerce1TestBase {

  use ValidateMigrationStateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_number_pattern',
    'commerce_order',
    'commerce_payment',
    'commerce_price',
    'commerce_product',
    'commerce_shipping',
    'commerce_store',
    'migrate_plus',
    'path',
    'physical',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->markTestSkipped("Profile module does not yet have a state file.");
    parent::setUp();
    $this->installEntitySchema('commerce_store');
  }

}
