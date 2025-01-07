<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the update of migrate table names.
 *
 * @group legacy
 */
class MigrateUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    if (explode('.', \Drupal::VERSION)[0] != '9') {
      $this->markTestSkipped();
    }
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-8.8.0.bare.standard.php.gz',
      __DIR__ . '/../../fixtures/drupal-8-update-8201.php',
    ];
  }

  /**
   * Test the update of the map table when the source plugin is uc7_field.
   */
  public function testUpdateTableNames() {
    foreach (['migration_group', 'migration'] as $entity_type) {
      $definition = \Drupal::entityTypeManager()
        ->getDefinition($entity_type);
      \Drupal::entityDefinitionUpdateManager()->installEntityType($definition);
    }
    $this->runUpdates();

    // Assert that a new source id has been added to the d7_field migrate map.
    $this->assertTrue(\Drupal::database()
      ->schema()
      ->fieldExists('migrate_map_d7_field', 'sourceid3'));
  }

}
