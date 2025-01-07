<?php

namespace Drupal\commerce_migrate_ubercart\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Gets the flat rate shipping service.
 *
 * @MigrateSource(
 *   id = "uc_shipping_flat_rate",
 *   source_module = "uc_flatrate"
 * )
 */
class ShippingFlatRate extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('uc_flatrate_methods', 'f')->fields('f');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'mid' => $this->t('Method id'),
      'title' => $this->t('Flat rate title'),
      'label' => $this->t('Flat rate label'),
      'base_rate' => $this->t('Base rate'),
      'product_rate' => $this->t('Product rate'),
      'currency_code' => $this->t('Currency code'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $currency_code = $this->variableGet('currency_code', 'USD');
    $row->setSourceProperty('currency_code', $currency_code);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'mid' => [
        'type' => 'integer',
      ],
    ];
  }

}
