<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc7;

use Drupal\commerce_product\Entity\Product;
use Drupal\Node\Entity\Node;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests Product migration.
 *
 * @requires migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
 */
class ProductTest extends Ubercart7TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'comment',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'filter',
    'image',
    'menu_ui',
    'migrate_plus',
    'node',
    'path',
    'path_alias',
    'taxonomy',
    'text',
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
    $this->assertProductEntity(1, 'product', '1', 'Breshtanti ale', TRUE, ['1'], ['1']);
    $variation = [
      'id' => 1,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'drink-001',
      'price' => '50.000000',
      'currency' => 'USD',
      'product_id' => '1',
      'title' => 'Breshtanti ale',
      'order_item_type_id' => 'default',
      'created_time' => '1493289860',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $product = Product::load(1);
    $this->assertEquals("Favored by all.", $product->body->value);
    $this->assertEquals("4", $product->field_number->value);
    $this->assertEquals("High", $product->field_sustainability->value);

    $this->assertProductEntity(2, 'product', '1', 'Romulan ale', TRUE, ['1'], ['2']);
    $variation = [
      'id' => 2,
      'type' => 'product',
      'uid' => '1',
      'sku' => 'drink-002',
      'price' => '100.000000',
      'currency' => 'USD',
      'product_id' => '2',
      'title' => 'Romulan ale',
      'order_item_type_id' => 'default',
      'created_time' => '1493326300',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    $product = Product::load(2);
    $this->assertEquals("Renowned", $product->body->value);

    $this->assertProductEntity(3, 'entertainment', '1', 'Holosuite 1', TRUE, ['1'], ['3']);
    $variation = [
      'id' => 3,
      'type' => 'entertainment',
      'uid' => '1',
      'sku' => 'Holosuite-001',
      'price' => '40.000000',
      'currency' => 'USD',
      'product_id' => '3',
      'title' => 'Holosuite 1',
      'order_item_type_id' => 'default',
      'created_time' => '1493326429',
      'changed_time' => NULL,
      'attributes' => NULL,
    ];
    $this->assertProductVariationEntity($variation);

    // There is only one node in the fixture that is not a product, node 4.
    $node = Node::load(4);
    $this->assertInstanceOf(Node::class, $node, "Node 4 exists.");

    // Nodes 1, 2 and 3 should not exist.
    $nodes = [1, 2, 3];
    foreach ($nodes as $node) {
      $node = Node::load($node);
      $this->assertNull($node, "Node $node exists.");
    }

  }

}
