<?php

namespace Drupal\Tests\commerce_migrate_csv_example\Unit\Plugin\migrate\process;

use Drupal\commerce_migrate_csv_example\Plugin\migrate\process\SuggestedProducts;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the suggested products process plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_csv_example\Plugin\migrate\process\SuggestedProducts
 *
 * @group commerce_migrate
 * @group commerce_migrate_csv_example
 */
class SuggestedProductsTest extends MigrateProcessTestCase {

  /**
   * Tests the suggested products process plugin.
   *
   * @dataProvider providerTestSuggestedProducts
   */
  public function testSuggestedProducts($value = NULL, $expected = NULL) {
    $configuration = [];
    $this->plugin = new SuggestedProducts($configuration, 'map', []);
    $value = $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testSuggestedProducts().
   */
  public function providerTestSuggestedProducts() {
    return [
      'one' => [
        ['one', 'two'],
        [
          [
            'one',
          ],
          [
            'two',
          ],
        ],
      ],
    ];
  }

}
