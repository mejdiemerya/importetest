<?php

namespace Drupal\Tests\commerce_migrate_magento\Kernel\Migrate\magento2;

use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateCoreTestTrait;
use Drupal\Tests\commerce_migrate\Kernel\CsvTestBase;

/**
 * Migrate category.
 *
 * @requires module migrate_source_csv
 *
 * @group commerce_migrate
 * @group commerce_migrate_magento2
 */
class TaxonomyTermTest extends CsvTestBase {

  use CommerceMigrateCoreTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'commerce_migrate',
    'commerce_migrate_magento',
    'taxonomy',
    'text',
    'user',
  ];

  /**
   * The cached taxonomy tree items, keyed by vid and tid.
   *
   * @var array
   */
  protected $treeData = [];

  /**
   * {@inheritdoc}
   */
  protected $fixtures = __DIR__ . '/../../../../fixtures/csv/magento2-catalog_product_20180326_013553.csv';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('taxonomy_term');
    $this->executeMigration('magento2_category');
    $this->executeMigration('magento2_category_term');
  }

  /**
   * Tests the Drupal 7 taxonomy term to Drupal 8 migration.
   */
  public function testTaxonomyTerm() {
    $description = <<<EOD
<p>The sporty Joust Duffle Bag can't be beat - not in the gym, not on the luggage carousel, not anywhere. Big enough to haul a basketball or soccer ball and some sneakers with plenty of room to spare, it's ideal for athletes with places to go.<p>
<ul>
<li>Dual top handles.</li>
<li>Adjustable shoulder strap.</li>
<li>Full-length zipper.</li>
<li>L 29" x W 13" x H 11".</li>
</ul>
EOD;
    $this->assertTermEntity(1, 'Gear', 'default_category', $description, NULL, 0, [0]);
    $description = <<<EOD
<p>The sporty Joust Duffle Bag can't be beat - not in the gym, not on the luggage carousel, not anywhere. Big enough to haul a basketball or soccer ball and some sneakers with plenty of room to spare, it's ideal for athletes with places to go.<p>
<ul>
<li>Dual top handles.</li>
<li>Adjustable shoulder strap.</li>
<li>Full-length zipper.</li>
<li>L 29" x W 13" x H 11".</li>
</ul>
EOD;
    $this->assertTermEntity(2, 'Bags', 'default_category', $description, NULL, 0, [1]);
    $description = <<<EOD
<p>Convenience is next to nothing when your day is crammed with action. So whether you're heading to class, gym, or the unbeaten path, make sure you've got your Strive Shoulder Pack stuffed with all your essentials, and extras as well.</p>
<ul>
<li>Zippered main compartment.</li>
<li>Front zippered pocket.</li>
<li>Side mesh pocket.</li>
<li>Cell phone pocket on strap.</li>
<li>Adjustable shoulder strap and top carry handle.</li>
</ul>
EOD;
    $this->assertTermEntity(3, 'Collections', 'default_category', $description, NULL, 0, [0]);
    $description = <<<EOD
<p>Perfect for class, work or the gym, the Wayfarer Messenger Bag is packed with pockets. The dual-buckle flap closure reveals an organizational panel, and the roomy main compartment has spaces for your laptop and a change of clothes. An adjustable shoulder strap and easy-grip handle promise easy carrying.</p>
<ul>
<li>Multiple internal zip pockets.</li>
<li>Made of durable nylon.</li>
</ul>
EOD;
    $this->assertTermEntity(4, 'New Luma Yoga Collection', 'default_category', $description, NULL, 0, [3]);
    $description = <<<EOD
<p>Beginner's Yoga starts you down the path toward strength, balance and mental focus. With this video download, you don't have to be a great athlete or gym guru to learn the best and most basic techniques for lifelong yoga foundation. </p>
<ul>
<li>Video download</li>
<li>Five workouts.</li>
<li>Balance, strength and endurance.</li>
<li>Flexibility and core strength.</li>
<li>Includes modification for novices.</li>
</ul>
EOD;
    $this->assertTermEntity(8, 'Video Download', 'default_category', $description, NULL, 0, [7]);
  }

}
