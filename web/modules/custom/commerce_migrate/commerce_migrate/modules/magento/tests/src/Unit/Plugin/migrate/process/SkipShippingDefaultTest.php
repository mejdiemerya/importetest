<?php

namespace Drupal\Tests\commerce_migrate_magento\Unit\Plugin\migrate\process;

use Drupal\commerce_migrate_magento\Plugin\migrate\process\CommercePrice;
use Drupal\commerce_migrate_magento\Plugin\migrate\process\SkipShippingDefault;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the skip default shipping row process plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_magento\Plugin\migrate\process\SkipShippingDefault
 *
 * @group commerce_migrate
 * @group commerce_migrate_magento2
 */
class SkipShippingDefaultTest extends MigrateProcessTestCase {

  /**
   * Tests the skip default shipping row process plugin.
   *
   * @dataProvider providerTestSkipShippingDefault
   */
  public function testSkipShippingDefault($value = NULL, $expected = NULL) {
    $configuration = [];
    $this->plugin = new SkipShippingDefault($configuration, 'map', []);
    $value = $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
    $this->assertSame($expected, $value);
  }

  /**
   * Data provider for testSkipShippingDefault().
   */
  public function providerTestSkipShippingDefault() {
    return [
      'billing and no shipping' => [
        [
          TRUE,
          FALSE,
        ],
        NULL,
      ],
    ];
  }

  /**
   * Tests that exception is thrown when input is not an array.
   *
   * @dataProvider providerTestShippingRow
   */
  public function testShippingRow($value = NULL) {
    $configuration = [];
    $this->plugin = new SkipShippingDefault($configuration, 'map', []);
    $this->expectExceptionMessage('Skip default shipping row.');
    $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
  }

  /**
   * Data provider for testShippingRow().
   */
  public function providerTestShippingRow() {
    return [
      'no billing and shipping' => [
        [
          FALSE,
          TRUE,
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
