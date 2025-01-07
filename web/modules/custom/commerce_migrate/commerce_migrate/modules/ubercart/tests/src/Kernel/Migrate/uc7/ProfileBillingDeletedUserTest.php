<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc7;

use Drupal\Core\Database\Database;
use Drupal\KernelTests\KernelTestBase;
use Drupal\profile\Entity\Profile;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;
use Drupal\Tests\migrate\Kernel\MigrateDumpAlterInterface;

/**
 * Tests customer profile migration.
 *
 * @requires module address
 * @requires module migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc7
 */
class ProfileBillingDeletedUserTest extends Ubercart7TestBase implements MigrateDumpAlterInterface {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_number_pattern',
    'commerce_order',
    'commerce_price',
    'commerce_store',
    'migrate_plus',
    'path',
    'profile',
    'state_machine',
    'telephone',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_store');
    $this->installSchema('commerce_number_pattern', ['commerce_number_pattern_sequence']);
    $this->installConfig('commerce_order');
    $this->migrateUsers(FALSE);
    $this->executeMigration('uc7_profile_billing');
  }

  /**
   * {@inheritdoc}
   */
  public static function migrateDumpAlter(KernelTestBase $test) {
    $db = Database::getConnection('default', 'migrate');
    // Change the uid of one of the profiles to a uid for a non-existent user.
    $db->update('uc_orders')
      ->condition('uid', '4')
      ->fields(['uid' => '400'])
      ->execute();
  }

  /**
   * Test profile migration.
   */
  public function testProfileBilling() {
    // Profile for order_id 1.
    $profile_id = 1;
    $this->assertProfile($profile_id, 'customer', '2', 'und', TRUE, TRUE, '1536902338', NULL);

    $profile = Profile::load($profile_id);
    $address = $profile->get('address')->first()->getValue();
    $this->assertAddressField($address, 'CA', NULL, 'Starship Voyager', NULL, '', NULL, 'Level 12', '', 'Tom', NULL, 'Paris', '');
    $phone = $profile->get('phone')->getValue();
    $this->assertSame([], $phone);

    // Profile for order_id 2.
    $profile_id = 2;
    $this->assertProfile($profile_id, 'customer', '0', 'und', FALSE, FALSE, '1536902428', NULL);
    $profile = Profile::load($profile_id);
    $address = $profile->get('address')->first()->getValue();
    $this->assertAddressField($address, 'CA', NULL, '', NULL, '', NULL, '', '', '', NULL, '', '');
    $phone = $profile->get('phone')->getValue();
    $this->assertSame([], $phone);

    // Tests the first revision of order 1.
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_revision */
    $profile_revision = \Drupal::entityTypeManager()->getStorage('profile')
      ->loadRevision(1);
    $address = $profile_revision->get('address')->first()->getValue();
    $this->assertAddressField($address, 'US', 'AL', 'San Francisco', NULL, '74656', NULL, '', '', 'Tom', NULL, 'Paris', '');
    $phone = $profile_revision->get('phone')->getValue()[0]['value'];
    $this->assertSame('555-4747', $phone);

    // Tests the first revision of order 2.
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_revision */
    $profile_revision = \Drupal::entityTypeManager()->getStorage('profile')
      ->loadRevision(2);
    $address = $profile_revision->get('address')->first()->getValue();
    $this->assertAddressField($address, 'US', 'CA', 'San Francisco', NULL, '', NULL, '33 First Street', '', 'Harry', NULL, 'Kim', '');
    $phone = $profile->get('phone')->getValue();
    $this->assertSame([], $phone);
  }

}
