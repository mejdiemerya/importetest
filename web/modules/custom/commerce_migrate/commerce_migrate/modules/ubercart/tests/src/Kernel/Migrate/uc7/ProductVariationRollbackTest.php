<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc7;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Tests rollback of product variation migration..
 *
 * @requires migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
 */
class ProductVariationRollbackTest extends ProductVariationTest {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('comment');
    $this->installSchema('comment', ['comment_entity_statistics']);
  }

  /**
   * Test product migration rollback.
   */
  public function testProductVariation() {
    // Rollback the product variations.
    $this->executeRollbacks(['uc7_product_variation']);

    $product_variation_ids = [1, 2, 3];
    foreach ($product_variation_ids as $product_variation_id) {
      $product_variation = ProductVariation::load($product_variation_id);
      $this->assertNull($product_variation, "Product variation $product_variation_id exists.");
    }

    for ($id = 1; $id <= 3; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertNull($product_variation, "Product variation $id exists.");
    }

    // Migrate products.
    $this->migrateProducts();
    for ($id = 1; $id <= 3; $id++) {
      $product = Product::load($id);
      $this->assertNotNull($product, "Product $id does not exist.");
    }

    for ($id = 4; $id <= 6; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertNotNull($product_variation, "Product variation $id does not exist.");
    }

    // Rollback the product variations.
    $this->executeRollbacks(['uc7_product_variation']);
    for ($id = 4; $id <= 6; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertNull($product_variation, "Product variation $id exists.");
    }
    for ($id = 1; $id <= 3; $id++) {
      $product = Product::load($id);
      $this->assertNotNull($product, "Product $id does not exist.");
    }

    $this->migrateProductVariations();
    for ($id = 1; $id <= 3; $id++) {
      $product = Product::load($id);
      $this->assertNotNull($product, "Product $id does not exist.");
    }

    for ($id = 7; $id <= 9; $id++) {
      $product_variation = ProductVariation::load($id);
      $this->assertNotNull($product_variation, "Product variation $id does not exist.");
    }
  }

}
