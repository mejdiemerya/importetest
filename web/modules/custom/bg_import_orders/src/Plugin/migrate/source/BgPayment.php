<?php

namespace Drupal\bg_import_orders\Plugin\migrate\source;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Gets Commerce  payment transactions from database.
 *
 * @MigrateSource(
 *   id = "bg_payment",
 *   source_module = "commerce_payment"
 * )
 */
class BgPayment  extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'transaction_id' => $this->t('Transaction ID'),
      'revision_id' => $this->t('Revision ID'),
      'uid' => $this->t('User ID'),
      'order_id' => $this->t('Order ID'),
      'payment_method' => $this->t('Payment Method'),
      'instance_id' => $this->t('Instance ID'),
      'remote_id' => $this->t('Remote ID'),
      'message' => $this->t('Message'),
      'message_variables' => $this->t('Message Variables'),
      'amount' => $this->t('Amount'),
      'currency_code' => $this->t('Currency Code'),
      'status' => $this->t('Status'),
      'remote_status' => $this->t('Remote Status'),
      'payload' => $this->t('Payload'),
      'created' => $this->t('Created'),
      'changed' => $this->t('Changed'),
      'data' => $this->t('Data'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['transaction_id']['type'] = 'integer';
    $ids['transaction_id']['alias'] = 'pt';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Process the receipts in chronological order.
    $query =  $this->select('commerce_payment_transaction', 'pt')
      ->fields('pt')
      ->orderBy('changed');
    $query->addJoin('inner','commerce_cardonfile', 'f1', 'f1.order_id = pt.order_id');
    $query->fields('f1', ['order_id','card_id']);
    // $query ->condition("pt.uid", 57813 );
echo $query->__toString();
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('transaction_id');
    $vid = $row->getSourceProperty('revision_id');
    foreach (array_keys($this->getFields('commerce_payment_transaction')) as $field) {
      $row->setSourceProperty($field, $this->getFieldValues('commerce_payment_transaction', $field, $nid, $vid));
    }
    if ($amount = $row->getSourceProperty('amount')) {
      $currencyRepository = new CurrencyRepository();
      $currency_code = $row->getSourceProperty('currency_code');
      $fraction_digits = $currencyRepository->get($currency_code)->getFractionDigits();
      $amount = bcdiv($amount, bcpow(10, $fraction_digits), $fraction_digits);

      //$amount['bundle'] = 'default';
      //$row->setSourceProperty('amount', $amount);

    }
    $row->setSourceProperty('amount', [
      'currency_code' => (!empty($currency_code)) ? $currency_code : 'USD',
      'number' => (!empty($amount)) ? $amount : 0,
    ]);

    $row->setSourceProperty('refunded_amount', [
      'currency_code' =>  (!empty($currency_code)) ? $currency_code : 'USD',
      'number' => 0,
    ]);
//    if($nid == 89939)
//var_dump($row);
    return parent::prepareRow($row);
  }

}
