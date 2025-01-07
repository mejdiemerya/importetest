<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Unit\Plugin\migrate\process;

use Drupal\commerce_migrate_ubercart\Plugin\migrate\process\OrderItemAdjustment;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Rounder;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\migrate\Row;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;

/**
 * Tests order item adjustment process plugin.
 *
 * @coversDefaultClass \Drupal\commerce_migrate_ubercart\Plugin\migrate\process\OrderItemAdjustment
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc
 */
class OrderItemAdjustmentTest extends MigrateProcessTestCase {

  /**
   * The mocked migration.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $migration;

  /**
   * The mocked migration.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $migrationPluginManager;

  /**
   * The mocked migration.
   *
   * @var \Drupal\core\Entity\EntityTypeManager|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $entityTypeManager;

  /**
   * The mocked migration.
   *
   * @var \Drupal\commerce_price\Rounder|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $rounder;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->migration = $this->prophesize(Migration::class);
    $this->migrationPluginManager = $this->prophesize(MigrationPluginManager::class);
    $this->entityTypeManager = $this->prophesize(EntityTypeManager::class);
    $this->rounder = $this->prophesize(Rounder::class);
  }

  /**
   * Tests valid input.
   *
   * @dataProvider providerTestTransform
   */
  public function testTransform($value, $num_product_line, $last_line, $rounder_output, $rounder_input, $rounder_output_2, $order_product_id, $max_order_product_id, $expected) {
    $row = $this->prophesize(Row::class);
    $row->getSourceProperty('num_product_line')
      ->willReturn($num_product_line);
    $row->getSourceProperty('order_product_id')
      ->willReturn($order_product_id);
    $row->getSourceProperty('max_order_product_id')
      ->willReturn($max_order_product_id);

    $price = new Price((string) $value['amount'], $value['currency_code']);
    $this->rounder->round($price)->willReturn($rounder_output);
    $this->rounder->round($rounder_input, PHP_ROUND_HALF_DOWN)->willReturn($rounder_output_2);

    $plugin = new TestOrderItemAdjustment([], 'test', [], $this->migration->reveal(), $this->migrationPluginManager->reveal(), $this->entityTypeManager->reveal(), $this->rounder->reveal());
    $result = $plugin->transform($value, $this->migrateExecutable, $row->reveal(), 'foo');
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testTransform().
   */
  public function providerTestTransform() {
    return [
      'one line item' => [
        [
          'line_item_id' => '6',
          'order_id' => '2',
          'type' => 'tax',
          'title' => 'Handling',
          'amount' => '60.00000',
          'weight' => '9',
          'data' =>
            [
              'tax_id' => '1',
              'tax_rate' => '0.04',
              'taxable_amount' => 1500.0,
              'tax_jurisdiction' => 'Handling',
            ],
          'currency_code' => 'NZD',
        ],
        1,
        TRUE,
        new Price(60.00, 'NZD'),
        new Price(60.00, 'NZD'),
        new Price(60.00, 'NZD'),
        2,
        2,
        [
          'type' => 'tax',
          'label' => 'Handling',
          'amount' => '60',
          'currency_code' => 'NZD',
          'percentage' => '0.04',
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ],
      ],
      'first of 2 line items' => [
        [
          'line_item_id' => '5',
          'order_id' => '1',
          'type' => 'generic',
          'title' => 'Service',
          'amount' => '1.99000',
          'weight' => '2',
          'data' => NULL,
          'currency_code' => 'NZD',
        ],
        2,
        FALSE,
        new Price(1.99, 'NZD'),
        new Price(0.995, 'NZD'),
        new Price(0.99, 'NZD'),
        3,
        4,
        [
          'type' => 'tax',
          'label' => 'Service',
          'amount' => '0.99',
          'currency_code' => 'NZD',
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ],
      ],
      'second of 2 line items' => [
        [
          'line_item_id' => '99',
          'order_id' => '1',
          'type' => 'tax',
          'title' => 'Handling',
          'amount' => '1.40000',
          'weight' => '9',
          'data' => [
            'tax_id' => '1',
            'tax_rate' => '0.04',
            'taxable_amount' => 35.0,
            'tax_jurisdiction' => 'Handling',
          ],
          'currency_code' => 'NZD',
        ],
        2,
        TRUE,
        new Price(1.40, 'NZD'),
        new Price(0.7, 'NZD'),
        new Price(0.7, 'NZD'),
        4,
        4,
        [
          'type' => 'tax',
          'label' => 'Handling',
          'amount' => '0.7',
          'currency_code' => 'NZD',
          'percentage' => '0.04',
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ],
      ],
      'promotion' => [
        [
          'line_item_id' => '7',
          'order_id' => '1',
          'type' => 'coupon',
          'title' => 'Handling',
          'amount' => '1.40000',
          'weight' => '9',
          'data' => [],
          'currency_code' => 'NZD',
        ],
        2,
        TRUE,
        new Price(1.40, 'NZD'),
        new Price(0.7, 'NZD'),
        new Price(0.70, 'NZD'),
        4,
        4,
        [
          'type' => 'promotion',
          'label' => 'Handling',
          'amount' => '0.7',
          'currency_code' => 'NZD',
          'percentage' => NULL,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ],
      ],
      'no product line' => [
        [
          'line_item_id' => '7',
          'order_id' => '1',
          'type' => 'tax',
          'title' => 'Handling',
          'amount' => '1.40000',
          'weight' => '9',
          'data' => [],
          'currency_code' => 'NZD',
        ],
        0,
        TRUE,
        new Price(1.40, 'NZD'),
        NULL,
        NULL,
        4,
        4,
        [],
      ],
    ];
  }

  /**
   * Tests valid input.
   *
   * @dataProvider providerTestSplit
   */
  public function testSplit($num_product_line, $last_line, $price, $split_rounder_input, $split_rounder_output, $expected) {
    $this->rounder->round($split_rounder_input, PHP_ROUND_HALF_DOWN)
      ->willReturn($split_rounder_output);

    $configuration = [];
    $this->plugin = new TestOrderItemAdjustment($configuration, 'map', [], $this->migration->reveal(), $this->migrationPluginManager->reveal(), $this->entityTypeManager->reveal(), $this->rounder->reveal());
    $result = $this->plugin->split($num_product_line, $last_line, $price);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testSplit().
   */
  public function providerTestSplit() {
    $zero_price = new Price(0, 'USD');
    $ten_price = new Price(10.00, 'USD');
    return [
      'no product line' => [
        0,
        FALSE,
        $zero_price,
        $zero_price,
        $zero_price,
        NULL,
      ],
      'one product line not last line' => [
        1,
        FALSE,
        $ten_price,
        $ten_price,
        $ten_price,
        $ten_price,
      ],
      'one product line last line' => [
        1,
        TRUE,
        $ten_price,
        $ten_price,
        $ten_price,
        $ten_price,
      ],
      'two product lines not last line' => [
        2,
        FALSE,
        $ten_price,
        new Price(5.00, 'USD'),
        new Price(5.00, 'USD'),
        new Price(5.00, 'USD'),
      ],
      'two product lines last line' => [
        2,
        TRUE,
        $ten_price,
        new Price(5.00, 'USD'),
        new Price(5.00, 'USD'),
        new Price(5.00, 'USD'),
      ],
      'three product lines not last line' => [
        3,
        FALSE,
        new Price(10.00, 'USD'),
        new Price(3.333333, 'USD'),
        new Price(3.33, 'USD'),
        new Price(3.33, 'USD'),
      ],
      'three product lines last line' => [
        3,
        TRUE,
        new Price(10.00, 'USD'),
        new Price(3.333333, 'USD'),
        new Price(3.33, 'USD'),
        new Price(3.34, 'USD'),
      ],
    ];
  }

}

/**
 * Test class for OrderItemDiscountAdjustment.
 */
class TestOrderItemAdjustment extends OrderItemAdjustment {

  /**
   * Sets the rounder.
   */
  public function setRounder($rounder) {
    $this->rounder = $rounder;
  }

  /**
   * {@inheritdoc}
   */
  // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
  public function split($num_product_line, $last_line, Price $price) {
    return parent::split($num_product_line, $last_line, $price);
  }

}
