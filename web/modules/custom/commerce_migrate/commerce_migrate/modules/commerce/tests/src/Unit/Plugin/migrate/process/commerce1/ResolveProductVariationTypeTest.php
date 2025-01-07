<?php

namespace Drupal\Tests\commerce_migrate_commerce\Unit\Plugin\migrate\process\commerce1;

use Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\ResolveProductVariationType;
use Drupal\migrate\Row;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the resolve target type process plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\ResolveProductVariationType
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class ResolveProductVariationTypeTest extends MigrateProcessTestCase {

  /**
   * Tests valid values.
   *
   * @dataProvider providerTestTransform
   */
  public function testTransform($value, $configuration, $referenceable_types, $expected) {
    $row = $this->prophesize(Row::class);
    $row->getSourceProperty('data/settings/referenceable_types')
      ->willReturn($referenceable_types);

    $this->plugin = new ResolveProductVariationType($configuration, 'map', []);
    $value = $this->plugin->transform($value, $this->migrateExecutable, $row->reveal(), 'foo');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testTransform().
   */
  public function providerTestTransform() {
    return [
      'count is one' => [
        'bags_cases',
        [],
        [
          'bags_cases' => 'bags_cases',
        ],
        'bags_cases',
      ],
      'no variations' => [
        'bags_cases',
        [],
        [
          'bags_cases' => 'bags',
          'drinks' => 'drinks',
        ],
        'bags_cases',
      ],
      'variations no matching' => [
        'bags_cases',
        [
          'variations' => [
            'default' => 'default',
          ],
        ],
        [
          'bags_cases',
          'drinks',
        ],
        'default',
      ],
      'variations with matching' => [
        'drinks',
        [
          'variations' => [
            'matching' => TRUE,
          ],
        ],
        [
          'tops',
          'bags_cases',
          'drinks',
        ],
        'drinks',
      ],
      'variations with matching no match' => [
        'shorts',
        [
          'variations' => [
            'matching' => FALSE,
          ],
        ],
        [
          'tops',
          'bags_cases',
          'drinks',
        ],
        'shorts',
      ],
    ];
  }

  /**
   * Tests that exception is thrown when input is not an array.
   *
   * @dataProvider providerTestWrongType
   */
  public function testWrongType($value) {
    $configuration = [];
    $this->plugin = new ResolveProductVariationType($configuration, 'map', []);
    $type = gettype($value);
    $this->expectExceptionMessage(sprintf("Input should be an string, instead it was of type '%s'", $type));
    $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
  }

  /**
   * Data provider for testWrongType().
   */
  public function providerTestWrongType() {
    $object = (object) [
      'one' => 'test1',
      'two' => 'test2',
      'three' => 'test3',
    ];
    return [
      'integer' => [
        1,
      ],
      'float' => [
        1.0,
      ],
      'NULL' => [
        NULL,
      ],
      'boolean' => [
        TRUE,
      ],
      'object' => [
        $object,
      ],
    ];
  }

}
