<?php

namespace Drupal\Tests\commerce_migrate_magento\Unit\Plugin\migrate\process;

use Drupal\commerce_migrate_magento\Plugin\migrate\process\CommercePrice;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the Commerce Price plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_magento\Plugin\migrate\process\CommercePrice
 *
 * @group commerce_migrate
 * @group commerce_migrate_magento2
 */
class CommercePriceTest extends MigrateProcessTestCase {

  /**
   * Tests Commerce Price plugin.
   *
   * @dataProvider providerTestCommercePrice
   */
  public function testCommercePrice($value = NULL, $expected = NULL) {
    $configuration = [];
    $this->plugin = new CommercePrice($configuration, 'map', []);
    $value = $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testCommercePrice().
   */
  public function providerTestCommercePrice() {
    return [
      'upper case currency' =>
        [
          [
            234,
            'NZD',
          ],
          [
            'number' => 234,
            'currency_code' => 'NZD',
          ],
        ],
      'lower case currency' =>
        [
          [
            234.5,
            'nzd',
          ],
          [
            'number' => 234.5,
            'currency_code' => 'NZD',
          ],
        ],
    ];
  }

  /**
   * Tests that exception is thrown when input is not an array.
   *
   * @dataProvider providerTestInvalidType
   */
  public function testInvalidType($value = NULL) {
    $configuration = [];
    $this->plugin = new CommercePrice($configuration, 'map', []);
    $type = gettype($value);
    $this->expectExceptionMessage(sprintf("Input should be an array, instead it was of type '%s'", $type));
    $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
  }

  /**
   * Data provider for testInvalidType().
   */
  public function providerTestInvalidType() {
    $xml_str = <<<XML
<?xml version='1.0'?>
<mathematician>
 <name>Ada Lovelace</name>
</mathematician>
XML;
    $object = (object) [
      'one' => 'test1',
      'two' => 'test2',
      'three' => 'test3',
    ];
    return [
      'empty string' => [
        '',
      ],
      'string' => [
        'Extract Test',
      ],
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
      'xml' => [
        $xml_str,
      ],
      'object' => [
        $object,
      ],
    ];
  }

}
