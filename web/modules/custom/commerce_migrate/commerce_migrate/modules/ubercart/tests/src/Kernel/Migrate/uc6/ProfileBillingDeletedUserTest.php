<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

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
 * @group commerce_migrate_uc6
 */
class ProfileBillingDeletedUserTest extends Ubercart6TestBase implements MigrateDumpAlterInterface {

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
    $this->executeMigration('uc6_profile_billing');
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
    $this->assertProfile($profile_id, 'customer', '3', 'und', TRUE, TRUE, '1492868907', NULL);
    $profile = Profile::load($profile_id);
    $address = $profile->get('address')->first()->getValue();
    $this->assertAddressField($address, 'US', NULL, '', NULL, '', NULL, '', '', '', NULL, '', '');

    // Profile for order_id 2.
    $profile_id = 2;
    $this->assertProfile($profile_id, 'customer', '5', 'und', TRUE, TRUE, '1492989920', NULL);
    $profile = Profile::load($profile_id);
    $address = $profile->get('address')->first()->getValue();
    $this->assertAddressField($address, 'US', 'WY', 'World B', NULL, '7654', NULL, '42 View Lane', 'Frogstar', 'Trin', NULL, 'Tragula', 'Perspective Ltd.');
    $phone = $profile->get('phone')->getValue()[0]['value'];
    $this->assertSame('111-9876', $phone);

    // Profile for order_id 3.
    $profile_id = 4;
    $this->assertProfile($profile_id, 'customer', '0', 'und', TRUE, TRUE, NULL, NULL);
    $profile = Profile::load($profile_id);
    $address = $profile->get('address')->first()->getValue();
    $this->assertAddressField($address, 'US', NULL, '', NULL, '', NULL, '', '', '', NULL, '', '');
    $phone = $profile->get('phone')->getValue();
    $this->assertSame([], $phone);

    // Profile for order_id 4.
    // Test the latest revision of order 3.
    $profile_id = 3;
    $this->assertProfile($profile_id, 'customer', '2', 'und', TRUE, TRUE, NULL, NULL);
    $profile = Profile::load($profile_id);
    $address = $profile->get('address')->first()->getValue();
    $this->assertAddressField($address, 'US', 'WY', 'World B', NULL, '7654', NULL, '42 View Lane', 'Frogstar', 'Trin', NULL, 'Tragula', 'Perspective Ltd.');
    $phone = $profile->get('phone')->getValue()[0]['value'];
    $this->assertSame('111-9876', $phone);

    // Tests the first revision of order 3.
    /** @var \Drupal\profile\Entity\ProfileInterface $profile_revision */
    $profile_revision = \Drupal::entityTypeManager()->getStorage('profile')
      ->loadRevision(4);
    $address = $profile_revision->get('address')->first()->getValue();
    $this->assertAddressField($address, 'GB', NULL, 'London', NULL, 'N1', NULL, '29 Arlington Avenue', '', 'Zaphod', NULL, 'Beeblebrox', '');
    $phone = $profile_revision->get('phone')->getValue()[0]['value'];
    $this->assertSame('226 7709', $phone);
  }

}
