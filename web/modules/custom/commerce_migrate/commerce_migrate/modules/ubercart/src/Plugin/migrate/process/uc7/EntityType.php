<?php

namespace Drupal\commerce_migrate_ubercart\Plugin\migrate\process\uc7;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Determines the entity type.
 *
 * If the PrepareRow event has set the commerce_product property then this row
 * is a product node.
 *
 * @MigrateProcessPlugin(
 *   id = "uc7_entity_type"
 * )
 */
class EntityType extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($row->getSourceProperty('commerce_product') === 1) {
      return 'commerce_product';

    }
    return $row->getSourceProperty('entity_type');
  }

}
