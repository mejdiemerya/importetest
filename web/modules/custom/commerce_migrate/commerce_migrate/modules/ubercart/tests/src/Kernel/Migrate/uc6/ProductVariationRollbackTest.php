<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Tests rollback of product variation migration..
 *
 * @requires migrate_plus
 *
 * @group commerce_migrate_uc6
 */
class ProductVariationRollbackTest extends ProductVariationTest {

  /**
   * Test product migration rollback.
   */
  public function testProductVariation() {
    // Rollback the product variations.
    $this->executeRollbacks(['uc6_product_variation']);

    $product_variation_ids = [1, 2, 3, 4, 5];
    foreach ($product_variation_ids as $product_variation_id) {
      $product_variation = ProductVariation::load($product_variation_id);
      $this->assertNull($product_variation, "Product variation $product_variation_id exists.");
    }

    for ($id = 1; $id <= 5; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertNull($product_variation, "Product variation $id exists.");
    }

    // Migrate products.
    $this->migrateProducts();
    for ($id = 1; $id <= 5; $id++) {
      $product = Product::load($id);
      $this->assertInstanceOf(Product::class, $product, "Product $id does not exist.");
    }

    for ($id = 6; $id <= 10; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertInstanceOf(ProductVariation::class, $product_variation, "Product variation $id does not exist.");
    }

    // Rollback the product variations.
    $this->executeRollbacks(['uc6_product_variation']);
    for ($id = 1; $id <= 10; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertNull($product_variation, "Product variation $id exists.");
    }
    for ($id = 1; $id <= 5; $id++) {
      $product = Product::load($id);
      $this->assertInstanceOf(Product::class, $product, "Product $id does not exist.");
    }

    $this->migrateProductVariations();
    for ($id = 1; $id <= 5; $id++) {
      $product = Product::load($id);
      $this->assertInstanceOf(Product::class, $product, "Product $id does not exist.");
    }

    for ($id = 11; $id <= 15; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertInstanceOf(ProductVariation::class, $product_variation, "Product variation $id does not exist.");
    }
  }

}
