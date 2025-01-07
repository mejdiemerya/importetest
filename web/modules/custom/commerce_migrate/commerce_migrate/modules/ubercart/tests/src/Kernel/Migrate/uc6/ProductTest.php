<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\commerce_product\Entity\Product;
use Drupal\Node\Entity\Node;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests Product migration.
 *
 * @requires migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class ProductTest extends Ubercart6TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'content_translation',
    'language',
    'menu_ui',
    'migrate_plus',
    'path',
    'path_alias',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->migrateProducts();
  }

  /**
   * Test product migration.
   */
  public function testProduct() {
    $this->assertProductEntity(1, 'product', '1', 'Bath Towel', TRUE, ['1'], ['1']);
    $variation = [
      'id' => 1,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'towel-bath-001',
      'price' => '20.000000',
      'currency' => 'NZD',
      'product_id' => '1',
      'title' => 'Bath Towel',
      'order_item_type_id' => 'default',
      'created_time' => '1492867780',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $this->assertProductEntity(2, 'product', '1', 'Beach Towel', TRUE, ['1'], ['2']);
    $variation = [
      'id' => 2,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'towel-beach-001',
      'price' => '15.000000',
      'currency' => 'NZD',
      'product_id' => '2',
      'title' => 'Beach Towel',
      'order_item_type_id' => 'default',
      'created_time' => '1492989418',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $this->assertProductEntity(3, 'product', '1', 'Fairy cake', TRUE, ['1'], ['3']);
    $variation = [
      'id' => 3,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'Fairy-Cake-001',
      'price' => '1500.000000',
      'currency' => 'NZD',
      'product_id' => '3',
      'title' => 'Fairy cake',
      'order_item_type_id' => 'default',
      'created_time' => '1492989703',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $product = Product::load(3);
    $this->assertEquals("Necessary ingredient for the Total Perspective Vortex.", $product->body->value);
    $this->assertEquals("5", $product->field_integer->value);
    $this->assertEquals("Low", $product->field_sustain->value);

    // There is only one node in the fixture that is not a product, node 6.
    $node = Node::load(6);
    $this->assertInstanceOf(Node::class, $node, "Node 6 exists.");

    // Nodes 1 to 5 and node 7 and 8 should not exist.
    $nodes = [1, 2, 3, 4, 5, 7, 8];
    foreach ($nodes as $node) {
      $node = Node::load($node);
      $this->assertNull($node, "Node $node exists.");
    }

    // Test that translations are working.
    $product = Product::load(1);
    $this->assertSame('en', $product->language()->getId());
    $this->assertTrue($product->hasTranslation('es'), "Product 1 missing the Spanish translation");
    $product = Product::load(2);
    $this->assertSame('und', $product->language()->getId());
    $this->assertFalse($product->hasTranslation('es'), "Product 2 should not have a Spanish translation");
    $product = Product::load(3);
    $this->assertSame('en', $product->language()->getId());
    $this->assertTrue($product->hasTranslation('es'), "Product 3 missing the Spanish translation");

    // Test that content_translation_source is set.
    $manager = $this->container->get('content_translation.manager');
    $this->assertSame('en', $manager->getTranslationMetadata($product->getTranslation('es'))
      ->getSource());
  }

}
