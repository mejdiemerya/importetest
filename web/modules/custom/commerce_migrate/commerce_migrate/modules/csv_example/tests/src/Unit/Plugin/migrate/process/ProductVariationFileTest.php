<?php

namespace Drupal\Tests\commerce_migrate_csv_example\Unit\Plugin\migrate\process;

use Drupal\commerce_migrate_csv_example\Plugin\migrate\process\ProductVariationFile;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the Product variation file process plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_csv_example\Plugin\migrate\process\ProductVariationFile
 *
 * @group commerce_migrate
 * @group commerce_migrate_csv_example
 */
class ProductVariationFileTest extends MigrateProcessTestCase {

  /**
   * Tests Product variation file process plugin.
   */
  public function testSuggestedProducts() {
    $executable = $this->prophesize(MigrateExecutableInterface::class)
      ->reveal();
    $row = $this->prophesize(Row::class)->reveal();
    $migration = $this->prophesize(MigrationInterface::class)->reveal();

    $value = [
      'fid' => 1,
      'list' => TRUE,
      'data' => serialize([]),
    ];
    $migrate_process = $this->prophesize(MigrateProcessInterface::class);
    $migrate_process->transform($value, $executable, $row, 'foo')
      ->willReturn(1);
    $plugin = new ProductVariationFile([], 'csv_example_image', [], $migration, $migrate_process->reveal());

    $transformed = $plugin->transform($value, $executable, $row, 'foo');
    $expected = [
      'target_id' => 1,
      'description' => '',
      'alt' => '',
      'title' => '',
    ];
    $this->assertSame($expected, $transformed);
  }

}
