<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Functional;

use Drupal\Tests\commerce_migrate\Functional\MigrateUpgradeTestTrait;
use Drupal\Tests\migrate_drupal_ui\Functional\MigrateUpgradeExecuteTestBase as CoreMigrateUpgradeExecuteTestBase;

/**
 * Base class for testing a migration run with the UI.
 */
abstract class MigrateUpgradeExecuteTestBase extends CoreMigrateUpgradeExecuteTestBase {

  use MigrateUpgradeTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->writeSettings([
      'settings' => [
        'migrate_node_migrate_type_classic' => (object) [
          'value' => TRUE,
          'required' => TRUE,
        ],
      ],
    ]);
  }

  /**
   * Executes all steps of migrations upgrade.
   */
  public function testMigrateUpgradeExecute() {
    $this->drupalGet('/upgrade');
    $session = $this->assertSession();
    $session->responseContains("Upgrade a site by importing its files and the data from its database into a clean and empty new install of Drupal $this->destinationSiteVersion.");

    $this->submitForm([], 'Continue');
    $session->pageTextContains('Provide credentials for the database of the Drupal site you want to upgrade.');

    // Get valid credentials.
    $edits = $this->translatePostValues($this->getCredentials());

    $this->submitForm($edits, 'Review upgrade');
    $session->statusCodeEquals(200);

    $this->submitForm([], 'I acknowledge I may lose data. Continue anyway.');
    $session->statusCodeEquals(200);

    $this->submitForm([], 'Perform upgrade');
    $this->assertUpgrade($this->getEntityCounts());
  }

}
