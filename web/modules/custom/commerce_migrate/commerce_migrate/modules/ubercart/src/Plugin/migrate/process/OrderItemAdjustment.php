<?php

namespace Drupal\commerce_migrate_ubercart\Plugin\migrate\process;

use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds an array of adjustment data.
 *
 * This plugin creates an adjustment array from data from uc_order_line_items.
 *
 * The input value:
 * - line_item_id: The Ubercart line item ID.
 * - order_id: The Ubercart order ID.
 * - type: The line item type.
 * - title: The line item title.
 * - amount: The amount.
 * - weight: The weight.
 * - data: The unserialized line item data.
 * - currency_code: The currency code.
 *
 * @code
 * adjustments:
 *    plugin: uc_order_item_adjustment
 *    source: adjustments
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "uc_order_item_adjustment"
 * )
 */
class OrderItemAdjustment extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $migrationPluginManager;

  /**
   * The migration to be executed.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The rounder.
   *
   * @var \Drupal\commerce_price\RounderInterface
   */
  protected $rounder;

  /**
   * The number.
   *
   * @var string
   */
  protected $number;

  /**
   * The currency code.
   *
   * @var string
   */
  protected $currencyCode;

  /**
   * Constructs a MigrationLookup object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The Migration the plugin is being used in.
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   *   The Migration Plugin Manager Interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   The rounder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, MigrationPluginManagerInterface $migration_plugin_manager, EntityTypeManagerInterface $entity_type_manager, RounderInterface $rounder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrationPluginManager = $migration_plugin_manager;
    $this->migration = $migration;
    $this->entityTypeManager = $entity_type_manager;
    $this->rounder = $rounder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('plugin.manager.migration'),
      $container->get('entity_type.manager'),
      $container->get('commerce_price.rounder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $adjustment = [];

    if (is_array($value)) {
      $adj_type = '';
      $percentage = NULL;
      if (!empty($value['type'])) {
        if ($value['type'] === 'tax' || $value['type'] === 'generic') {
          $adj_type = 'tax';
          $percentage = !empty($value['data']['tax_rate']) ? $value['data']['tax_rate'] : $percentage;
        }
        if ($value['type'] === 'coupon') {
          $adj_type = 'promotion';
        }
      }

      if ($adj_type === '') {
        throw new MigrateSkipRowException(sprintf("Unknown adjustment type '%s' for line item '%s'.", $value['type'], $value['line_item_id']));
      }
      $label = !empty($value['title']) ? $value['title'] : '';

      // Distribute the adjustment across all product line items.
      $num_product_line = $row->getSourceProperty('num_product_line');

      $price = new Price((string) $value['amount'], $value['currency_code']);
      $price = $this->rounder->round($price);

      $last_line = FALSE;
      if ($row->getSourceProperty('order_product_id') == $row->getSourceProperty('max_order_product_id')) {
        $last_line = TRUE;
      }
      $amount = $this->split($num_product_line, $last_line, $price);

      if ($amount) {
        $adjustment = [
          'type' => $adj_type,
          'label' => $label,
          'amount' => $amount->getNumber(),
          'currency_code' => $amount->getCurrencyCode(),
          'percentage' => $percentage,
          'source_id' => 'custom',
          'included' => FALSE,
          'locked' => TRUE,
        ];
      }
    }
    return $adjustment;
  }

  /**
   * Computes the percentage of a price to apply to this line.
   *
   * @param string $num_product_line
   *   The current product line number.
   * @param bool $last_line
   *   Indicates if this is the last line item for the order.
   * @param \Drupal\commerce_price\Price $price
   *   The rounded total price for this sline item.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The price to apply to this line of NULL if there was an error.
   */
  protected function split($num_product_line, $last_line, Price $price) {
    $individual_amount = NULL;
    if ($num_product_line > 0) {
      // Get the amount to add to each product line item.
      $percentage = 1 / $num_product_line;
      $percentage = new Price((string) $percentage, $price->getCurrencyCode());

      // Calculate the initial per-order-item amounts using the percentage.
      // Round down to ensure that their sum isn't larger than the full amount.
      $individual_amount = $price->multiply($percentage->getNumber());
      $individual_amount = $this->rounder->round($individual_amount, PHP_ROUND_HALF_DOWN);

      // Make any adjustments needed in the last line item for this order.
      if ($last_line) {
        $price_calculated = $individual_amount->multiply($num_product_line);
        $difference = $price->subtract($price_calculated);
        if (!$difference->isZero()) {
          $individual_amount = $individual_amount->add($difference);
        }
      }
    }
    return $individual_amount;
  }

}
