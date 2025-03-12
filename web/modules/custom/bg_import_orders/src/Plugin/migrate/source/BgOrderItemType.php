<?php

namespace Drupal\bg_import_orders\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Gets Commerce 1 order item types from database.
 *
 * @MigrateSource(
 *   id = "bg_order_item_type",
 *   source_module = "commerce_order"
 * )
 */
class BgOrderItemType extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('commerce_line_item', 'cli')
      ->fields('cli', ['type'])
      ->distinct();
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'type' => $this->t('Type'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['type']['type'] = 'string';
    return $ids;
  }

}
