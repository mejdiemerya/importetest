<?php

namespace Drupal\commerce_migrate_ubercart\Plugin\migrate\source\uc7;

use Drupal\field\Plugin\migrate\source\d7\Field as D7Field;

/**
 * Ubercart 7 field source from database.
 *
 * Add an initializeIterator() method so that rows can be added when a field
 * exists on a product node and any other entity. The added rows are solely to
 * create such a field on a Commerce 2 commerce_product entity.
 *
 * @MigrateSource(
 *   id = "uc7_field",
 *   source_module = "field_sql_storage"
 * )
 */
class Field extends D7Field {

  use ProductTypeTrait;

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $this->productTypes = $this->getProductTypes();

    $results = $this->prepareQuery()->execute();
    $rows = [];
    foreach ($results as $result) {
      // Get all the instances of this field.
      $field_name = $result['field_name'];
      // Get all the instances of this field.
      $query = $this->select('field_config_instance', 'fci')
        ->fields('fci', ['bundle'])
        ->condition('fc.active', 1)
        ->condition('fc.storage_active', 1)
        ->condition('fc.deleted', 0)
        ->condition('fci.deleted', 0)
        ->condition('fci.entity_type', 'node');
      $query->join('field_config', 'fc', 'fci.field_id = fc.id');
      $query->condition('fci.field_name', $field_name);
      $node_bundles = $query->execute()->fetchCol();

      // Determine if the field is on both a product type and node, or just one
      // of product type or node.
      $product_node_count = 0;
      foreach ($node_bundles as $bundle) {
        if (in_array($bundle, $this->productTypes)) {
          $product_node_count++;
        }
      }

      $node_count = 0;
      foreach ($node_bundles as $bundle) {
        if ($bundle === 'node') {
          $node_count++;
        }
      }

      $result['commerce_product'] = 0;
      if ($product_node_count > 0) {
        // If all bundles for this field are product types, then add the
        // commerce product flag to indicate this is a product node.
        if ($product_node_count == count($node_bundles)) {
          $result['commerce_product'] = 1;
        }
        else {
          // This field is on both a product node and a non product node so add
          // a new row to create the field storage on the commerce_product
          // entity.
          $add_row = $result;
          $add_row['commerce_product'] = 1;
          $rows[] = $add_row;
        }
      }
      $rows[] = $result;
    }
    return new \ArrayIterator($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = parent::fields();
    $fields['commerce_product'] = $this->t('Product node flag');
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = parent::getIds();
    return $ids + [
      // Add a flag to indicate a commerce product node. This third source id
      // is not required for existing migration_lookups on d7_field to work
      // correctly.
      'commerce_product' => [
        'type' => 'integer',
      ],
    ];
  }

}
