<?php

namespace Drupal\Tests\commerce_migrate\Kernel;

use Drupal\KernelTests\FileSystemModuleDiscoveryDataProviderTrait;
use Drupal\Tests\migrate_drupal\Kernel\MigrateDrupalTestBase;

/**
 * Tests that labels exist for all migrations.
 *
 * @group commerce_migrate
 */
class CommerceMigrationLabelExistTest extends MigrateDrupalTestBase {

  use FileSystemModuleDiscoveryDataProviderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_store',
    'commerce_migrate',
    'commerce_migrate_commerce',
    'commerce_migrate_ubercart',
  ];

  /**
   * Migration plugin tags to create instances for.
   *
   * @var array
   */
  protected $tags = [
    'Ubercart',
    'Commerce',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    // Add field tables for the d7_field_instance source plugin used in
    // _commerce_migrate_commerce_get_attributes used in
    // commerce_migrate_commerce_migration_plugins_alter.
    $this->sourceDatabase->schema()->createTable('field_config', [
      'fields' => [
        'id' => [
          'type' => 'serial',
          'not null' => TRUE,
          'size' => 'normal',
        ],
        'field_name' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '32',
        ],
        'type' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '128',
        ],
        'module' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '128',
          'default' => '',
        ],
        'active' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
        'storage_type' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '128',
        ],
        'storage_module' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '128',
          'default' => '',
        ],
        'storage_active' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
        'locked' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
        'data' => [
          'type' => 'blob',
          'not null' => TRUE,
          'size' => 'big',
        ],
        'cardinality' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
        'translatable' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
        'deleted' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
      ],
      'primary key' => [
        'id',
      ],
      'indexes' => [
        'field_name' => [
          'field_name',
        ],
        'active' => [
          'active',
        ],
        'storage_active' => [
          'storage_active',
        ],
        'deleted' => [
          'deleted',
        ],
        'module' => [
          'module',
        ],
        'storage_module' => [
          'storage_module',
        ],
        'type' => [
          'type',
        ],
        'storage_type' => [
          'storage_type',
        ],
      ],
      'mysql_character_set' => 'utf8',
    ]);
    $this->sourceDatabase->insert('field_config')
      ->fields([
        'id',
        'field_name',
        'type',
        'module',
        'active',
        'storage_type',
        'storage_module',
        'storage_active',
        'locked',
        'data',
        'cardinality',
        'translatable',
        'deleted',
      ])
      ->values([
        'id' => '1',
        'field_name' => 'commerce_customer_address',
        'type' => 'addressfield',
        'module' => 'addressfield',
        'active' => '1',
        'storage_type' => 'field_sql_storage',
        'storage_module' => 'field_sql_storage',
        'storage_active' => '1',
        'locked' => '0',
        'data' => 'a:6:{s:12:"entity_types";a:1:{i:0;s:25:"commerce_customer_profile";}s:12:"translatable";b:0;s:8:"settings";a:0:{}s:7:"storage";a:4:{s:4:"type";s:17:"field_sql_storage";s:8:"settings";a:0:{}s:6:"module";s:17:"field_sql_storage";s:6:"active";i:1;}s:12:"foreign keys";a:0:{}s:7:"indexes";a:0:{}}',
        'cardinality' => '1',
        'translatable' => '0',
        'deleted' => '0',
      ])
      ->execute();
    $this->sourceDatabase->schema()->createTable('field_config_instance', [
      'fields' => [
        'id' => [
          'type' => 'serial',
          'not null' => TRUE,
          'size' => 'normal',
        ],
        'field_id' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
        ],
        'field_name' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '32',
          'default' => '',
        ],
        'entity_type' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '32',
          'default' => '',
        ],
        'bundle' => [
          'type' => 'varchar',
          'not null' => TRUE,
          'length' => '128',
          'default' => '',
        ],
        'data' => [
          'type' => 'blob',
          'not null' => TRUE,
          'size' => 'big',
        ],
        'deleted' => [
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'tiny',
          'default' => '0',
        ],
      ],
      'primary key' => [
        'id',
      ],
      'indexes' => [
        'field_name_bundle' => [
          'field_name',
          'entity_type',
          'bundle',
        ],
        'deleted' => [
          'deleted',
        ],
      ],
      'mysql_character_set' => 'utf8',
    ]);
    $this->sourceDatabase->insert('field_config_instance')
      ->fields([
        'id',
        'field_id',
        'field_name',
        'entity_type',
        'bundle',
        'data',
        'deleted',
      ])
      ->values([
        'id' => '1',
        'field_id' => '1',
        'field_name' => 'commerce_customer_address',
        'entity_type' => 'commerce_customer_profile',
        'bundle' => 'billing',
        'data' => 'a:6:{s:5:"label";s:7:"Address";s:8:"required";b:1;s:6:"widget";a:4:{s:4:"type";s:21:"addressfield_standard";s:6:"weight";i:-10;s:8:"settings";a:3:{s:15:"format_handlers";a:2:{i:0;s:7:"address";i:1;s:12:"name-oneline";}s:19:"available_countries";a:0:{}s:15:"default_country";s:12:"site_default";}s:6:"module";s:12:"addressfield";}s:7:"display";a:3:{s:7:"default";a:5:{s:5:"label";s:6:"hidden";s:4:"type";s:20:"addressfield_default";s:6:"weight";i:-10;s:8:"settings";a:2:{s:19:"use_widget_handlers";i:1;s:15:"format_handlers";a:1:{i:0;s:7:"address";}}s:6:"module";s:12:"addressfield";}s:8:"customer";a:5:{s:5:"label";s:6:"hidden";s:4:"type";s:20:"addressfield_default";s:6:"weight";i:-10;s:8:"settings";a:2:{s:19:"use_widget_handlers";i:1;s:15:"format_handlers";a:1:{i:0;s:7:"address";}}s:6:"module";s:12:"addressfield";}s:13:"administrator";a:5:{s:5:"label";s:6:"hidden";s:4:"type";s:20:"addressfield_default";s:6:"weight";i:-10;s:8:"settings";a:2:{s:19:"use_widget_handlers";i:1;s:15:"format_handlers";a:1:{i:0;s:7:"address";}}s:6:"module";s:12:"addressfield";}}s:8:"settings";a:1:{s:18:"user_register_form";b:0;}s:11:"description";s:0:"";}',
        'deleted' => '0',
      ])
      ->execute();
  }

  /**
   * Tests that labels exist for all migrations.
   */
  public function testLabelExist() {
    // Install all available modules.
    $module_handler = $this->container->get('module_handler');
    $modules = $this->coreModuleListDataProvider();
    $modules_enabled = $module_handler->getModuleList();
    $modules_to_enable = array_keys(array_diff_key($modules, $modules_enabled));
    $this->enableModules($modules_to_enable);

    /** @var \Drupal\migrate\Plugin\MigrationPluginManager $plugin_manager */
    $plugin_manager = $this->container->get('plugin.manager.migration');
    // Get all the commerce_migrate migrations.
    $migrations = [];
    foreach ($this->tags as $tag) {
      $migrations = array_merge($migrations, $plugin_manager->createInstancesByTag($tag));
    }

    /** @var \Drupal\migrate\Plugin\Migration $migration */
    foreach ($migrations as $migration) {
      $migration_id = $migration->getPluginId();
      $this->assertNotEmpty($migration->label(), 'Label not found for ' . $migration_id);
    }
  }

}
