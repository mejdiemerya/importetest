<?php

namespace Drupal\Tests\commerce_migrate_csv_example\Unit\Plugin\migrate\process;

use Drupal\commerce_migrate_csv_example\Plugin\migrate\process\AttributeDestination;
use Drupal\migrate\MigrateException;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests the Attribute Destination process plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_csv_example\Plugin\migrate\process\AttributeDestination
 *
 * @group commerce_migrate
 * @group commerce_migrate_csv_example
 */
class AttributeDestinationTest extends MigrateProcessTestCase {

  /**
   * Tests Attribute Destination plugin.
   *
   * @dataProvider providerTestAttributeDestination
   */
  public function testAttributeDestination($value = NULL, $expected = NULL) {
    $configuration = [];
    $this->row = $this->createMock('Drupal\migrate\Row');

    $this->row
      ->expects($this->once())
      ->method('get')
      ->with("attribute_$value[0]")
      ->willReturn($value[1]);

    $this->plugin = new AttributeDestination($configuration, 'map', []);
    $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');

    $property = $this->row->get($expected[0]);
    $this->assertSame($expected[1], $property);
  }

  /**
   * Data provider for testAttributeDestination().
   */
  public function providerTestAttributeDestination() {
    return [
      'one' => [
        ['able', 'baker'],
        ['attribute_able', 'baker'],
      ],
    ];
  }

  /**
   * Tests that exception is thrown when input is not valid.
   *
   * @dataProvider providerTestInvalidValue
   */
  public function testInvalidValue($value = NULL) {
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage("There must be an even number of input values.");
    $this->plugin = new AttributeDestination([], 'test_format_date', []);
    $this->plugin->transform($value, $this->migrateExecutable, $this->row, 'foo');
  }

  /**
   * Data provider for testInvalidValue().
   */
  public function providerTestInvalidValue() {
    return [
      'one argument' => [
        ['a'],
      ],
      'three arguments' => [
        ['a', 'b', 'c'],
      ],
    ];

  }

}
