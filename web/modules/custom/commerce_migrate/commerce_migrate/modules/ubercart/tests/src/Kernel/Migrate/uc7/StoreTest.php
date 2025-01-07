<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc7;

use Drupal\commerce_store\Entity\Store;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests store migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
 */
class StoreTest extends Ubercart7TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'address',
    'commerce_price',
    'commerce_store',
    'path',
    'path_alias',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->migrateStore();
  }

  /**
   * Test store migration.
   */
  public function testStore() {
    $this->assertStoreEntity(1, "Quark's", 'quark@example.com', 'USD', 'online', '1', TRUE);

    $store = Store::load(1);
    $address = $store->getAddress();
    $this->assertAddressItem($address, 'CA', '', 'Deep Space 9', '', '9999', '', '47 The Promenade', 'Lower Level', '', '', '', '');
  }

}
