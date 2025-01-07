<?php
// @codingStandardsIgnoreFile

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Set the schema version.
$modules = [
  'commerce_migrate_ubercart' => 8000,
  'commerce_migrate' => 8000,
  'migrate' => 8001,
  'migrate_drupal' => 8601,
  'migrate_plus' => 8100,
];
foreach ($modules as $module => $schema_version) {
  $connection->merge('key_value')
    ->fields([
      'value' => serialize($schema_version),
      'name' => $module,
      'collection' => 'system.schema',
    ])
    ->condition('collection', 'system.schema')
    ->condition('name', $schema_version)
    ->execute();
}
// Update core.extension.
$extensions = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();
$extensions = unserialize($extensions);
foreach ($modules as $module => $schema_version) {
  $extensions['module'][$module] = 0;
}
$connection->update('config')
  ->fields([
    'data' => serialize($extensions),
    'collection' => '',
    'name' => 'core.extension',
  ])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute();

// Create migration table with legacy names.
$legacy_table_names = [
  'migrate_map_d7_field',
];

foreach ($legacy_table_names as $legacy_table_name) {
  $connection->schema()->createTable($legacy_table_name, [
    'fields' => [
      'source_ids_hash' => [
        'type' => 'varchar',
        'not null' => TRUE,
        'length' => '64',
      ],
      'sourceid1' => [
        'type' => 'varchar',
        'not null' => TRUE,
        'length' => '255',
      ],
      'sourceid2' => [
        'type' => 'varchar',
        'not null' => TRUE,
        'length' => '255',
      ],
      'destid1' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'length' => '255',
      ],
      'destid2' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'length' => '255',
      ],
      'source_row_status' => [
        'type' => 'int',
        'not null' => TRUE,
        'size' => 'tiny',
        'default' => '0',
        'unsigned' => TRUE,
      ],
      'rollback_action' => [
        'type' => 'int',
        'not null' => TRUE,
        'size' => 'tiny',
        'default' => '0',
        'unsigned' => TRUE,
      ],
      'last_imported' => [
        'type' => 'int',
        'not null' => TRUE,
        'size' => 'normal',
        'default' => '0',
        'unsigned' => TRUE,
      ],
      'hash' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'length' => '64',
      ],
    ],
  ]);
}
