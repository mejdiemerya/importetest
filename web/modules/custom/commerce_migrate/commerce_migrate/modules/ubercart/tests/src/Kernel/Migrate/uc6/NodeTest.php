<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\commerce_product\Entity\Product;
use Drupal\node\Entity\Node;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests Product migration.
 *
 * @requires module migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class NodeTest extends Ubercart6TestBase {

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
    $this->migrateProductVariations();
  }

  /**
   * Test product migration.
   */
  public function testProduct() {
    // Checks that the Ubercart product node id are not migrated.
    $node = Node::load(1);
    $this->assertNull($node);
    $node = Node::load(2);
    $this->assertNull($node);
    $node = Node::load(3);
    $this->assertNull($node);
    $node = Node::load(4);
    $this->assertNull($node);
    $node = Node::load(5);
    $this->assertNull($node);

    // Assert the page node is migrated as a node.
    $node = Node::load(6);
    $this->assertInstanceOf(Node::class, $node);

    // Assert the products.
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

    $this->assertProductEntity(4, 'ship', '1', 'Golgafrincham B-Ark', TRUE, ['1'], ['4']);
    $variation = [
      'id' => 4,
      'type' => 'ship',
      'uid' => '1',
      'sku' => 'ship-001',
      'price' => '6000000000.000000',
      'currency' => 'NZD',
      'product_id' => '4',
      'title' => 'Golgafrincham B-Ark',
      'order_item_type_id' => 'default',
      'created_time' => '1500868190',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $this->assertProductEntity(5, 'ship', '1', 'Heart of Gold', TRUE, ['1'], ['5']);
    $variation = [
      'id' => 5,
      'type' => 'ship',
      'uid' => '1',
      'sku' => 'ship-002',
      'price' => '123000000.000000',
      'currency' => 'NZD',
      'product_id' => '5',
      'title' => 'Heart of Gold',
      'order_item_type_id' => 'default',
      'created_time' => '1500868361',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    // Checks that the products are not duplicated. This can happen if the node
    // revision migration is executed for a product node.
    $product = Product::load(6);
    $this->assertNull($product);
    $product = Product::load(7);
    $this->assertNull($product);
  }

}
