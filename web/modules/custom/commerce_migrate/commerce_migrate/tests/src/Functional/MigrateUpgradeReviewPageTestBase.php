<?php

namespace Drupal\Tests\commerce_migrate\Functional;

use Drupal\migrate_drupal\MigrationConfigurationTrait;
use Drupal\Tests\migrate_drupal\Traits\CreateTestContentEntitiesTrait;
use Drupal\Tests\migrate_drupal_ui\Functional\MultilingualReviewPageTestBase;

/**
 * Provides a base class for testing the review step of the Upgrade form.
 */
abstract class MigrateUpgradeReviewPageTestBase extends MultilingualReviewPageTestBase {

  use MigrationConfigurationTrait;
  use CreateTestContentEntitiesTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate_drupal_ui'];

  /**
   * Tests the migrate upgrade review form.
   */
  public function testMigrateUpgradeReviewPage() {
    $this->prepare();
    // Start the upgrade process.
    $this->drupalGet('/upgrade');
    $this->submitForm([], 'Continue');

    // Get valid credentials.
    $edits = $this->translatePostValues($this->getCredentials());

    $this->submitForm($edits, 'Review upgrade');

    // Test the upgrade paths.
    $this->assertReviewForm();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCounts() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCountsIncremental() {
    return [];
  }

}
