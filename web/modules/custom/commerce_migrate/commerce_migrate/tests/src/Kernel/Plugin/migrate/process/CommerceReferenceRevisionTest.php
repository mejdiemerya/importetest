<?php

namespace Drupal\Tests\commerce_migrate\Kernel\Plugin\migrate\process;

use Drupal\commerce_migrate\Plugin\migrate\process\CommerceReferenceRevision;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\MigrateStub;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the CommerceReferenceRevision plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate\Plugin\migrate\process\CommerceReferenceRevision
 *
 * @group commerce_migrate
 */
class CommerceReferenceRevisionTest extends MigrateProcessTestCase {

  /**
   * Tests a successful lookup.
   *
   * @dataProvider providerTestCommerceReferenceRevision
   */
  public function testCommerceReferenceRevision($value, $transformed, $expected) {
    $migration = $this->prophesize(MigrationInterface::class);
    $migrate_lookup = $this->prophesize(MigrateLookupInterface::class);
    $migrate_lookup->lookup('test', $value)->willReturn($transformed);

    $migrate_stub = $this->prophesize(MigrateStub::class);
    $migrate_executable = $this->prophesize(MigrateExecutable::class);
    $row = $this->prophesize(Row::class);

    $configuration = [];
    $configuration['migration'] = 'test';
    $plugin = new CommerceReferenceRevision($configuration, 'test', [], $migration->reveal(), $migrate_lookup->reveal(), $migrate_stub->reveal());

    $result = $plugin->transform($value, $migrate_executable->reveal(), $row->reveal(), 'destination');
    $this->assertSame($expected, $result);
  }

  /**
   * Provides data for the successful lookup test.
   *
   * @return array
   *   The data.
   */
  public function providerTestCommerceReferenceRevision() {
    return [
      'return array' => [
        [1],
        [[3, 4]],
        ['target_id' => 3, 'target_revision_id' => 4],
      ],
      'return null' => [
        [1],
        NULL,
        NULL,
      ],
      'return string' => [
        [1],
        [['3']],
        NULL,
      ],
    ];
  }

}
