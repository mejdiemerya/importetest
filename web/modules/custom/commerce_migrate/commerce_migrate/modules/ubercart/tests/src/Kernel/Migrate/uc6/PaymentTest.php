<?php

namespace Drupal\Tests\commerce_migrate_ubercart\Kernel\Migrate\uc6;

use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests payment migration.
 *
 * @requires module migrate_plus
 *
 * @group commerce_migrate
 * @group commerce_migrate_uc6
 */
class PaymentTest extends Ubercart6TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_number_pattern',
    'commerce_order',
    'commerce_payment',
    'commerce_price',
    'commerce_product',
    'commerce_store',
    'content_translation',
    'language',
    'migrate_plus',
    'node',
    'path',
    'path_alias',
    'profile',
    'state_machine',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('commerce_payment');
    PaymentGateway::create([
      'id' => 'example',
      'label' => 'Example',
      'plugin' => 'manual',
    ])->save();

    $this->migrateOrders();
    $this->executeMigrations([
      'uc_payment_gateway',
      'uc6_payment',
    ]);
  }

  /**
   * Tests payment migration.
   */
  public function testPayment() {
    $payment = [
      'id' => 1,
      'order_id' => '1',
      'type' => 'payment_manual',
      'payment_gateway' => 'check',
      'payment_method' => NULL,
      'amount_number' => '37.990000',
      'amount_currency_code' => 'NZD',
      'refunded_amount_number' => '0.000000',
      'refunded_amount_currency_code' => 'NZD',
      'balance_number' => '37.99',
      'balance_currency_code' => 'NZD',
      'label_value' => 'new',
      'label_rendered' => 'New',
    ];
    $this->assertPaymentEntity($payment);
    $payment = [
      'id' => 2,
      'order_id' => '2',
      'type' => 'payment_manual',
      'payment_gateway' => 'cod',
      'payment_method' => NULL,
      'amount_number' => '2000.000000',
      'amount_currency_code' => 'NZD',
      'refunded_amount_number' => '1700.000000',
      'refunded_amount_currency_code' => 'NZD',
      'balance_number' => '300',
      'balance_currency_code' => 'NZD',
      'label_value' => 'partially_refunded',
      'label_rendered' => 'Partially refunded',
    ];
    $this->assertPaymentEntity($payment);
    $payment = [
      'id' => 4,
      'order_id' => '2',
      'type' => 'payment_manual',
      'payment_gateway' => 'cod',
      'payment_method' => NULL,
      'amount_number' => '50.000000',
      'amount_currency_code' => 'NZD',
      'refunded_amount_number' => '0.000000',
      'refunded_amount_currency_code' => 'NZD',
      'balance_number' => '50',
      'balance_currency_code' => 'NZD',
      'label_value' => 'new',
      'label_rendered' => 'New',
    ];
    $this->assertPaymentEntity($payment);
    $payment = [
      'id' => 6,
      'order_id' => '3',
      'type' => 'payment_manual',
      'payment_gateway' => 'cod',
      'payment_method' => NULL,
      'amount_number' => '12.000000',
      'amount_currency_code' => 'NZD',
      'refunded_amount_number' => '12.000000',
      'refunded_amount_currency_code' => 'NZD',
      'balance_number' => '0',
      'balance_currency_code' => 'NZD',
      'label_value' => 'refunded',
      'label_rendered' => 'Refunded',
    ];
    $this->assertPaymentEntity($payment);

    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $this->getMigration('uc6_payment');

    // Check that we've reported the refund in excess of payments.
    $messages = [];
    foreach ($migration->getIdMap()->getMessages() as $message_row) {
      $messages[] = $message_row->message;
    }
    $this->assertCount(1, $messages);
    $this->assertSame('Refund exceeds payments for payment 6', $messages[0]);
  }

}
