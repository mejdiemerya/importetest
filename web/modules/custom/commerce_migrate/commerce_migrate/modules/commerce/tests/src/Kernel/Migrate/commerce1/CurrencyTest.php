<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests currency migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class CurrencyTest extends Commerce1TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_price',
    'commerce_store',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->executeMigration('commerce1_currency');
  }

  /**
   * Test currency migration from Commerce 1 to Commerce 2.
   */
  public function testCurrency() {
    $this->assertCurrencyEntity('USD', 'USD', 'US Dollar', '840', 2, '$');
  }

}
