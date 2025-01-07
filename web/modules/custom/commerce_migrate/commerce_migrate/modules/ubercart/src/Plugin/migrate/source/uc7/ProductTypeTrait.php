<?php

namespace Drupal\commerce_migrate_ubercart\Plugin\migrate\source\uc7;

/**
 * Gets the product node types from the source database.
 */
trait ProductTypeTrait {

  /**
   * Product node types.
   *
   * @var array
   */
  protected $productTypes = [];

  /**
   * Helper to get the product types from the source database.
   *
   * @return array
   *   The product types.
   */
  protected function getProductTypes() {
    if (!empty($this->productTypes)) {
      return $this->productTypes;
    }
    $query = $this->select('node_type', 'nt')
      ->fields('nt', ['type'])
      ->condition('module', 'uc_product%', 'LIKE')
      ->distinct();
    $this->productTypes = [$query->execute()->fetchCol()];
    return reset($this->productTypes);
  }

}
