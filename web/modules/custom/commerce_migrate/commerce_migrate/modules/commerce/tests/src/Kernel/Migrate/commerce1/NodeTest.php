<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\node\NodeInterface;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateCoreTestTrait;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests node migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class NodeTest extends Commerce1TestBase {

  use CommerceMigrateTestTrait;
  use CommerceMigrateCoreTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'comment',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'datetime',
    'image',
    'link',
    'menu_ui',
    'migrate_plus',
    'node',
    'path',
    'path_alias',
    'taxonomy',
    'telephone',
    'text',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('comment');
    $this->installEntitySchema('taxonomy_term');
    $this->installSchema('comment', ['comment_entity_statistics']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('commerce_product_attribute_value');

    $this->migrateFiles();
    $this->migrateUsers();
    $this->migrateFields();
    $this->migrateTaxonomyTerms();
    $this->migrateProducts();
    $this->executeMigrations([
      'd7_node',
    ]);
    $this->nodeStorage = $this->container->get('entity_type.manager')
      ->getStorage('node');
  }

  /**
   * Test node migration from Commerce 1 to Commerce Drupal 8.
   */
  public function testNode() {
    // Confirm there are only complete node migration map tables. This shows
    // that only the complete migration ran.
    $results = $this->nodeMigrateMapTableCount('7');
    $this->assertSame(5, $results['node']);

    $db = \Drupal::database();
    $this->assertEquals($this->expectedNodeFieldRevisionTable(), $db->select('node_field_revision', 'nr')
      ->fields('nr')
      ->orderBy('vid')
      ->orderBy('langcode')
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC));
    $this->assertEquals($this->expectedNodeFieldDataTable(), $db->select('node_field_data', 'nr')
      ->fields('nr')
      ->orderBy('nid')
      ->orderBy('vid')
      ->orderBy('langcode')
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC));

    // Test field values on node 11.
    $node_storage = \Drupal::service('entity_type.manager')->getStorage('node');
    $revision = $node_storage->loadRevision(11);
    $this->assertInstanceOf(NodeInterface::class, $revision);
    $this->assertSame('99', $revision->get('field_image')
      ->getValue()[0]['target_id']);
    $this->assertSame('internal:/drinks/guy-h20', $revision->get('field_link')
      ->getValue()[0]['uri']);
    $this->assertSame("You're getting thirsty", $revision->get('field_tagline')
      ->getValue()[0]['value']);

    // Test values of a product variation field.
    $variation = ProductVariation::load(1);
    $this->assertInstanceOf(ProductVariation::class, $variation);
    $expected = [
      [
        'target_id' => '1',
        'alt' => NULL,
        'title' => NULL,
        'width' => '860',
        'height' => '842',
      ],
      [
        'target_id' => '2',
        'alt' => NULL,
        'title' => NULL,
        'width' => '860',
        'height' => '1251',
      ],
      [
        'target_id' => '3',
        'alt' => NULL,
        'title' => NULL,
        'width' => '860',
        'height' => '1100',
      ],
    ];
    $actual = $variation->get('field_images')->getValue();
    $this->assertCount(3, $actual);
    $target_id = array_column($actual, 'target_id');
    array_multisort($target_id, SORT_ASC, SORT_NUMERIC, $actual);
    $this->assertSame($expected, $actual);

    // Test values of a product field.
    $product = Product::load(15);
    $this->assertInstanceOf(Product::class, $product);
    $this->assertCount(1, $product->get('field_category')->getValue());
    $this->assertSame('50', $product->get('field_category')
      ->getValue()[0]['target_id']);

    // Test vocabulary and terms for non-product terms.
    $this->assertVocabularyEntity('blog_category', 'Blog category', '', NULL, 0);
    $this->assertVocabularyEntity('tags', 'Tags', 'Use tags to group blog posts on similar topics.', NULL, 0);

    $this->assertTermEntity(44, 'Kickstart Tip', 'blog_category', '', NULL, 0, [0]);
    $this->assertTermEntity(45, 'Social', 'tags', '', NULL, 0, [0]);
    $this->assertTermEntity(46, 'Kickstart', 'tags', '', NULL, 0, [0]);
    $this->assertTermEntity(47, 'CMT', 'blog_category', '', NULL, 0, [0]);
  }

  /**
   * Returns the expected data for the node_field_data table.
   */
  public function expectedNodeFieldDataTable() {
    return [
      [
        'nid' => '1',
        'vid' => '1',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Contact',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '2',
        'vid' => '2',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'About',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '3',
        'vid' => '3',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Terms of Use',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '4',
        'vid' => '4',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Payment',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '5',
        'vid' => '5',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Shipping fees',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '6',
        'vid' => '6',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Press links',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '7',
        'vid' => '7',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Service agreements',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '8',
        'vid' => '8',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Our security policy',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '9',
        'vid' => '9',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => '403 error',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '10',
        'vid' => '10',
        'type' => 'page',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => '404 error',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '11',
        'vid' => '11',
        'type' => 'ad_push',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Ad push - Getting thirsty',
        'created' => '1493287378',
        'changed' => '1493287378',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '12',
        'vid' => '12',
        'type' => 'ad_push',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Ad push - Go green',
        'created' => '1493287378',
        'changed' => '1493287378',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '13',
        'vid' => '13',
        'type' => 'blog_post',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Social Logins Made Simple',
        'created' => '1493287383',
        'changed' => '1493287383',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '14',
        'vid' => '14',
        'type' => 'blog_post',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'CMT - Commerce Customizable Products',
        'created' => '1493287383',
        'changed' => '1493287383',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '35',
        'vid' => '35',
        'type' => 'slideshow',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => "The latest & greatest what's new",
        'created' => '1493287427',
        'changed' => '1493287427',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '36',
        'vid' => '36',
        'type' => 'slideshow',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => "get it quick because... it's hot",
        'created' => '1493287427',
        'changed' => '1493287427',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '37',
        'vid' => '37',
        'type' => 'slideshow',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Go with one of our staff picks',
        'created' => '1493287427',
        'changed' => '1493287427',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
    ];

  }

  /**
   * Returns the expected data for the node_field_revision table.
   */
  public function expectedNodeFieldRevisionTable() {
    return [
      [
        'nid' => '1',
        'vid' => '1',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Contact',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '2',
        'vid' => '2',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'About',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '3',
        'vid' => '3',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Terms of Use',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '4',
        'vid' => '4',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Payment',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '5',
        'vid' => '5',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Shipping fees',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '6',
        'vid' => '6',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Press links',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '7',
        'vid' => '7',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Service agreements',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '8',
        'vid' => '8',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Our security policy',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '9',
        'vid' => '9',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => '403 error',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '10',
        'vid' => '10',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => '404 error',
        'created' => '1493287345',
        'changed' => '1493287345',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '11',
        'vid' => '11',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Ad push - Getting thirsty',
        'created' => '1493287378',
        'changed' => '1493287378',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '12',
        'vid' => '12',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Ad push - Go green',
        'created' => '1493287378',
        'changed' => '1493287378',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '13',
        'vid' => '13',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Social Logins Made Simple',
        'created' => '1493287383',
        'changed' => '1493287383',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '14',
        'vid' => '14',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'CMT - Commerce Customizable Products',
        'created' => '1493287383',
        'changed' => '1493287383',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '35',
        'vid' => '35',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => "The latest & greatest what's new",
        'created' => '1493287427',
        'changed' => '1493287427',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '36',
        'vid' => '36',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => "get it quick because... it's hot",
        'created' => '1493287427',
        'changed' => '1493287427',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
      [
        'nid' => '37',
        'vid' => '37',
        'langcode' => 'und',
        'status' => '1',
        'uid' => '1',
        'title' => 'Go with one of our staff picks',
        'created' => '1493287427',
        'changed' => '1493287427',
        'promote' => '0',
        'sticky' => '0',
        'default_langcode' => '1',
        'revision_translation_affected' => '1',
      ],
    ];
  }

}
