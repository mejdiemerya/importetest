<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Tests rollback of Product migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class ProductRollbackTest extends ProductTest {

  /**
   * Test product migration rollback.
   */
  public function testProduct() {
    $this->executeRollbacks(['commerce1_product']);

    for ($id = 15; $id <= 34; $id++) {
      $product = Product::load($id);
      $this->assertNull($product, "Product $id exists.");
    }

    for ($id = 1; $id <= 84; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertInstanceOf(ProductVariation::class, $product_variation, "Product variation $id exists.");
    }
  }

}
