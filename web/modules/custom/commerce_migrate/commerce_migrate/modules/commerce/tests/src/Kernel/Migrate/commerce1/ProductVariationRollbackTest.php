<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Tests rollback of Product Variation migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class ProductVariationRollbackTest extends ProductVariationTest {

  /**
   * Test product migration rollback.
   */
  public function testProductVariation() {
    // Rollback the product variations.
    $this->executeRollbacks(['commerce1_product_variation']);

    for ($id = 1; $id <= 84; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertNull($product_variation, "Product variation $id exists.");
    }

    // Migrate products.
    $this->migrateProducts();
    for ($id = 15; $id <= 34; $id++) {
      $product = Product::load($id);
      $this->assertInstanceOf(Product::class, $product, "Product $id does not exist.");
    }

    for ($id = 1; $id <= 84; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertInstanceOf(ProductVariation::class, $product_variation, "Product variation $id does not exist.");
    }

    // Rollback the product variations.
    $this->executeRollbacks(['commerce1_product_variation']);
    for ($id = 1; $id <= 84; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertNull($product_variation, "Product variation $id exists.");
    }
    for ($id = 15; $id <= 34; $id++) {
      $product = Product::load($id);
      $this->assertInstanceOf(Product::class, $product, "Product $id does not exist.");
    }

    $this->migrateProductVariations();
    for ($id = 15; $id <= 34; $id++) {
      $product = Product::load($id);
      $this->assertInstanceOf(Product::class, $product, "Product $id does not exist.");
    }

    for ($id = 1; $id <= 84; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertInstanceOf(ProductVariation::class, $product_variation, "Product variation $id does not exist.");
    }
  }

}
