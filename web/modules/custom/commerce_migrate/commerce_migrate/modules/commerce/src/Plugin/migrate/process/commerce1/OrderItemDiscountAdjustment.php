<?php

namespace Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1;

use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds an array of adjustment data.
 *
 * The input value:
 *  The source order_component data.
 *
 * @MigrateProcessPlugin(
 *   id = "commerce1_order_item_discount_adjustment"
 * )
 */
class OrderItemDiscountAdjustment extends CommercePrice implements ContainerFactoryPluginInterface {

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

    // Find all price components on the order, not the line item, that are not
    // a shipping type and not also in the line item price components.
    $order_components = $row->getSourceProperty('order_components/0/data/components');

    $order_names = [];
    foreach ($order_components as $order_component) {
      $order_names[] = $order_component['name'];
    }

    $shipping = $row->getSourceProperty('shipping');
    $not_shipping = [];
    if (!empty($order_names)) {
      $shipping_names = [];
      foreach ($shipping as $shipping_item) {
        $data = unserialize($shipping_item['data']);
        $shipping_names[] = $data['shipping_service']['price_component'];
      }
      $not_shipping = array_diff($order_names, $shipping_names);
    }

    if (!empty($not_shipping)) {
      foreach ($not_shipping as $item) {
        if ($value['name'] === $item) {
          $adjustment = $this->getAdjustment($value, $migrate_executable, $row);
        }
      }
    }
    return $adjustment;
  }

  /**
   * Get the adjustments on this order item.
   *
   * @param mixed $value
   *   The value to be transformed.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process. Normally, just transforming the value
   *   is adequate but very rarely you might need to change two columns at the
   *   same time or something like that.
   *
   * @return array
   *   An array of adjustment data.
   *
   * @throws \Drupal\migrate\MigrateSkipRowException
   */
  protected function getAdjustment($value, MigrateExecutableInterface $migrate_executable, Row $row) {
    $adjustment = [];
    if (is_array($value)) {
      if ($value['name'] !== 'base_price') {
        $parts = explode('|', $value['name'], -1);
        if (!empty($parts)) {
          $percentage = NULL;
          $type = '';
          $label = '';
          $amount = (string) $value['price']['amount'];
          $currency_code = $value['price']['currency_code'];
          if ($parts[0] === 'tax') {
            $type = 'tax';
            $tax_rate = $value['price']['data']['tax_rate'];
            $label = $tax_rate['display_title'];
            $percentage = $tax_rate['rate'];
          }
          if ($parts[0] === 'discount') {
            $type = 'promotion';
            $label = $value['price']['data']['discount_component_title'];
          }
          if (empty($type)) {
            throw new MigrateSkipRowException(sprintf("Unknown adjustment type for line item '%s'", $row->getSourceProperty('line_item_id')));
          }
          // Scale the incoming price by the fraction digits.
          $fraction_digits = $value['price']['fraction_digits'] ?? '2';
          $input = [
            'amount' => $amount,
            'fraction_digits' => $fraction_digits,
            'currency_code' => $currency_code,
          ];
          $price_scaled = parent::transform($input, $migrate_executable, $row, NULL);
          $price = new Price((string) $price_scaled['number'], $price_scaled['currency_code']);
          $price = $this->rounder->round($price);

          $num_product_line = $row->getSourceProperty('num_product_line');
          $last_line = FALSE;
          if ($row->getSourceProperty('line_item_id') == $row->getSourceProperty('max_line_item_id')) {
            $last_line = TRUE;
          }
          $amount = $this->split($num_product_line, $last_line, $price);
          if ($amount) {
            $adjustment = [
              'type' => $type,
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
      }
    }
    return $adjustment;
  }

  /**
   * Prorates the price amount over the number of line items.
   *
   * @param string $num_product_line
   *   The product line number of this product.
   * @param bool $last_line
   *   True when this is the last product line item for the order. The last
   *   line item contains any adjustment to the calculated necessary for the
   *   total adjustment to be correct.
   * @param \Drupal\commerce_price\Price $price
   *   The calculated adjustment to apply to this line item.
   *
   * @return \Drupal\commerce_price\Price
   *   The price to apply to this line or NULL if there was an error. The last
   *   line item contains any adjustment to the price so that the total
   *   adjustment is correct.
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
