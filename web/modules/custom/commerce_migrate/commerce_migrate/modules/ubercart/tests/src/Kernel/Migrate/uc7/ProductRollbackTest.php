<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc7;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Tests rollback of Product migration.
 *
 * @requires migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
 */
class ProductRollbackTest extends ProductTest {

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
  public function testProduct() {
    $this->executeRollbacks([
      'd7_node:entertainment',
      'd7_node:product',
    ]);

    $product_ids = [1, 2, 3];
    foreach ($product_ids as $product_id) {
      $product = Product::load($product_id);
      $this->assertNull($product, "Product $product_id exists.");
    }

    $product_variation_ids = [1, 2, 3];
    foreach ($product_variation_ids as $product_variation_id) {
      $product_variation = ProductVariation::load($product_variation_id);
      $this->assertInstanceOf(ProductVariation::class, $product_variation, "Product variation $product_variation_id does not exist.");
    }
  }

}
