<?php

namespace Drupal\Tests\commerce_migrate_commerce\Kernel\Migrate\commerce1;

use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\Tests\commerce_migrate\Kernel\CommerceMigrateTestTrait;

/**
 * Tests payment gateway migration.
 *
 * @group commerce_migrate
 * @group commerce_migrate_commerce1
 */
class PaymentGatewayTest extends Commerce1TestBase {

  use CommerceMigrateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_order',
    'commerce_payment',
    'commerce_price',
    'commerce_store',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->executeMigration('commerce1_payment_gateway');
  }

  /**
   * Asserts a payment gateway entity.
   *
   * @param string $id
   *   The payment gateway id.
   * @param string $label
   *   The payment gateway label.
   * @param int $weight
   *   The payment gateway weight.
   */
  private function assertPaymentGatewayEntity($id, $label, $weight) {
    $gateway = PaymentGateway::load($id);
    $this->assertInstanceOf(PaymentGateway::class, $gateway);
    $this->assertSame($label, $gateway->label());
    $this->assertSame($weight, $gateway->getWeight());
  }

  /**
   * Tests payment migration.
   */
  public function testPayment() {
    $this->assertPaymentGatewayEntity('commerce_payment_example', 'commerce_payment_example', NULL);
  }

}
