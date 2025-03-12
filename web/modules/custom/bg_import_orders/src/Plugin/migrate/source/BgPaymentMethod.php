<?php

namespace Drupal\bg_import_orders\Plugin\migrate\source;


use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Gets Commerce  payment transactions from database.
 *
 * @MigrateSource(
 *   id = "bg_payment_method",
 *   source_module = "commerce_payment"
 * )
 */
class BgPaymentMethod  extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [

    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['card_id']['type'] = 'integer';
    $ids['card_id']['alias'] = 'pt';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Process the receipts in chronological order.
    $query =  $this->select('commerce_cardonfile', 'pt')
      ->fields('pt');
    $query->addJoin('left','commerce_payment_transaction', 'f1', 'f1.order_id  = pt.order_id');
    $query ->fields('f1', ["created","changed"] );
    $query->addJoin('left','field_data_commerce_cardonfile_profile', 'f2', 'f2.entity_id  = pt.card_id');
    $query ->fields('f2', ["commerce_cardonfile_profile_profile_id" ] );

    // $query ->condition("pt.uid", 57813 );
 //echo $query->__toString();
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    $card_exp_month = $row->getSourceProperty('card_exp_month');
    $card_exp_year = $row->getSourceProperty('card_exp_year');
    $specified_date = new \DateTime("$card_exp_year-$card_exp_month-01");
    $last_day_of_specified_month = $specified_date->format('t');

    $expires = mktime(23,59,59, $card_exp_month, $last_day_of_specified_month, $card_exp_year);

    $row->setSourceProperty('expires', $expires);
    return parent::prepareRow($row);
  }

}
