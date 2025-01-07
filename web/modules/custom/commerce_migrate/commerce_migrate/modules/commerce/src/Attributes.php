<?php

namespace Drupal\commerce_migrate_commerce;

use Drupal\migrate\Plugin\MigrationDeriverTrait;

/**
 * Get the attribute rows from the d7_field_instance source plugin.
 */
class Attributes {

  use MigrationDeriverTrait;

  /**
   * Get d7_field_instance rows that are for product attributes.
   *
   * @return \Drupal\migrate\Row[]
   *   The source plugin rows that are for taxonomy term reference fields, that
   *   are using options select and for commerce products.
   */
  public static function getAttributeRows() {
    $source_plugin = self::getSourcePlugin('d7_field_instance');
    return array_filter(iterator_to_array($source_plugin), function ($row) {
      return (($row->getSourceProperty('entity_type') == 'commerce_product') &&
        ($row->getSourceProperty('type') == 'taxonomy_term_reference') &&
        ($row->getSourceProperty('widget')['type'] == 'options_select'));
    });
  }

}
