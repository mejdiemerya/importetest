<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\commerce_store\Entity\Store;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests store migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class StoreTest extends Ubercart6TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
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
    $this->assertStoreEntity(1, 'Awesome Stuff', 'awesome_stuff@example.com', 'NZD', 'online', '1', TRUE);

    $store = Store::load(1);
    $address = $store->getAddress();
    $this->assertAddressItem($address, 'US', '', 'Betelgeuse', '', '4242', '', '123 First Street', '456 Second Street', '', '', '', '');
  }

}
