<?php

namespace Drupal\bg_import_orders\Plugin\migrate\field;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\field\FieldPluginBase;

/**
 * Field migration for commerce_line item.
 *
 * @MigrateField(
 *   id = "bg_line_item_reference",
 *   type_map = {
 *     "bg_line_item_reference" = "bg_order_item"
 *   },
 *   core = {7},
 *   source_module = "bg_line_item",
 *   destination_module = "commerce_order"
 * )
 */
class CommerceLineItemReferenceField extends FieldPluginBase {
  /**
   * Field name map.
   *
   * @var array
   *   The field names on orders are different in Commerce 2 than Commerce 1.
   */
  public $fieldNameMap =
    [
      'commerce_line_items' => 'order_items',
    ];

  /**
   * {@inheritdoc}
   */
  public function processFieldValues(MigrationInterface $migration, $field_name, $data) {
    $this->defineValueProcessPipeline($migration, $field_name, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function defineValueProcessPipeline(MigrationInterface $migration, $field_name, $data) {
    $destination_field_name = $this->fieldNameMap[$field_name] ?? $field_name;
    $process = [
      [
        'plugin' => 'migration_lookup',
        'migration' => 'bg_order_item',
        'source' => $field_name,
        'no_stub' => TRUE,
      ],
      [
        'plugin' => 'skip_on_empty',
        'method' => 'process',
      ],
    ];
    $migration->setProcessOfProperty($destination_field_name, $process);
  }

}
