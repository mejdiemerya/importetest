<?php

namespace Drupal\commerce_migrate_commerce\Plugin\migrate\source\commerce1;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Gets Commerce 1 commerce_product_type data from database.
 *
 * @MigrateSource(
 *   id = "commerce1_product_display_type",
 *   source_module = "commerce_product"
 * )
 */
class ProductDisplayType extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'field_name' => $this->t('Product reference field name'),
      'type' => $this->t('Type'),
      'name' => $this->t('Name'),
      'description' => $this->t('Description'),
      'help' => $this->t('Help'),
      'data' => $this->t('Product reference field instance data'),
      'variation_type' => $this->t('Product variation type'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['type']['type'] = 'string';
    $ids['type']['alias'] = 'nt';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $row->setSourceProperty('data', unserialize($row->getSourceProperty('data')));
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('field_config', 'fc');
    $query->leftJoin('field_config_instance', 'fci', '(fci.field_id = fc.id)');
    $query->leftJoin('node_type', 'nt', '(nt.type = fci.bundle)');
    $query->condition('fc.type', 'commerce_product_reference')
      ->condition('fc.active', 1)
      ->condition('fci.entity_type', 'node')
      ->condition('nt.disabled', 0);
    $query->fields('fc', ['field_name'])
      ->fields('fci', ['data'])
      ->fields('nt', ['type', 'name', 'description', 'help']);
    return $query;
  }

}
