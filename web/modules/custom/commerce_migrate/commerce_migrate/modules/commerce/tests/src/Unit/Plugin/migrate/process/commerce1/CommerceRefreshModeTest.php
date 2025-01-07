<?php

namespace Drupal\Tests\commerce_migrate_commerce\Unit\Plugin\migrate\process\commerce1;

use Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\CommerceRefreshMode;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the CommerceRefreshMode plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1\CommerceRefreshMode
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class CommerceRefreshModeTest extends MigrateProcessTestCase {

  /**
   * Tests CommerceRefreshMode plugin based on providerTestSubstr() values.
   *
   * @dataProvider providerTestCommercePrice
   */
  public function testCommercePrice($value = NULL, $expected = NULL) {
    $configuration = [];
    $this->plugin = new CommerceRefreshMode($configuration, 'map', []);
    $value = $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testSubstr().
   */
  public function providerTestCommercePrice() {
    // Valid cases.
    $tests[0]['value'] = ['always', 'not_owner_only'];
    $tests[0]['new_value'] = 'always';

    $tests[0]['value'] = ['always', 'owner_only'];
    $tests[0]['new_value'] = 'always';

    $tests[1]['value'] = [FALSE, 'not_owner_only'];
    $tests[1]['new_value'] = 'customer';

    $tests[2]['value'] = [FALSE, 'owner_only'];
    $tests[2]['new_value'] = 'customer';

    // Invalid input, string.
    $tests[3]['value'] = 'string';
    $tests[3]['new_value'] = NULL;

    // Invalid input, integer.
    $tests[4]['value'] = 1;
    $tests[4]['new_value'] = NULL;

    // Invalid input, empty array.
    $tests[5]['value'] = [];
    $tests[5]['new_value'] = NULL;

    return $tests;
  }

}
