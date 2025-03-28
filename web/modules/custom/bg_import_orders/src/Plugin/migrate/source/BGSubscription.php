<?php

namespace Drupal\bg_import_orders\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal 7   source from database.
 *
 * @MigrateSource(
 *   id = "bg_subscription",
 *   source_module = "commerce_license"
 * )
 */
class BGSubscription extends FieldableEntity {

  /**
   * The join options between commerce_customer_profile and its revision table.
   */
  const JOIN = 'cp.revision_id = cpr.revision_id';

  //
  public function getBillingCycleType($product_id) {
    $query = $this->select('field_data_cl_billing_cycle_type', 'cpr')
      ->fields('cpr');
    $query ->condition("cpr.entity_id", $product_id );

    $query->range(0,1);
     $result = $query->execute()->fetchObject();

     if(!empty($result->cl_billing_cycle_type_target_id)){
       if ($result->cl_billing_cycle_type_target_id == 1) {
         return 'billing_annual';
       }else{
           return 'billing_monthly';

       }
     }
    return null;

  }
  public function getOrderId($license_id) {
    $query = $this->select('field_data_cl_billing_license', 'cpr')
      ->fields('cpr');
    $query ->condition("cpr.cl_billing_license_target_id", $license_id );

    $query->addJoin('left','commerce_line_item', 'f4', 'f4.line_item_id = cpr.entity_id');
    $query->addField('f4', 'order_id', 'originating_order');
    $query->orderBy('f4.order_id' ,'DESC');
    $query->range(0,1);
     $result = $query->execute()->fetchObject();
     if(!empty($result->originating_order))
     return $result->originating_order;

     return null;
  }
  public function getLastOrderId($license_id) {
    $query = $this->select('field_data_cl_billing_license', 'cpr')
      ->fields('cpr');
    $query ->condition("cpr.cl_billing_license_target_id", $license_id );

    $query->addJoin('left','commerce_line_item', 'f4', 'f4.line_item_id = cpr.entity_id');
    $query->addField('f4', 'order_id', 'originating_order');
    $query->orderBy('f4.order_id' ,'DESC');
    $query->range(0,1);
     $result = $query->execute()->fetchObject();
     if(!empty($result->originating_order))
     return $result->originating_order;

     return null;
  }
  public function getLastActiveOrderId($license_id) {
    $query = $this->select('field_data_cl_billing_license', 'cpr')
      ->fields('cpr');
    $query ->condition("cpr.cl_billing_license_target_id", $license_id );

    $query->addJoin('left','commerce_line_item', 'f4', 'f4.line_item_id = cpr.entity_id');
    $query->addField('f4', 'order_id', 'originating_order');
    $query->addJoin('left','commerce_order', 'f5', 'f5.order_id = f4.order_id');
    $query->addField('f5', 'status', 'status');
    $query->orderBy('f4.order_id' ,'DESC');
    $query ->condition("f5.status", 'recurring_open' );
    $query->range(0,1);
     $result = $query->execute()->fetchObject();
     if(!empty($result->originating_order))
     return $result->originating_order;

     return null;
  }
  public function getOrderAmount($originating_order) {
    $query = $this->select('field_data_commerce_order_total', 'cpr')
      ->fields('cpr');
    $query ->condition("cpr.entity_id", $originating_order );

     $result = $query->execute()->fetchObject();
     if(!empty($result->commerce_order_total_amount))
     return $result->commerce_order_total_amount/100;

     return null;
  }
  public function fetchCiBilling($originating_order,$field) {
    $query = $this->select('field_data_cl_billing_cycle', 'cpr')
      ->fields('cpr');
    $query ->condition("cpr.entity_id", $originating_order );
    $query->addJoin('left','cl_billing_cycle', 'f4', 'f4.billing_cycle_id = cpr.cl_billing_cycle_target_id');
    $query->fields('f4', ['start','end']);
     $result = $query->execute()->fetchObject();
     if(!empty($result ))
     return $result->{$field} ;

     return null;
  }
  /**
   * {@inheritdoc}
   */
  //
  public function query() {
    $query = $this->select('commerce_license', 'cpr')
      ->fields('cpr');
    $query->addJoin('inner','commerce_product', 'f4', 'f4.product_id = cpr.product_id');
    $query->addField('f4', 'title', 'product_title');
    //$query->addJoin('inner',' field_data_cl_billing_license', 'f5', 'f5.cl_billing_license_target_id = cpr.license_id');
    //$query->addJoin('inner',' commerce_line_item', 'f6', 'f5.entity_id = f6.line_item_id');
   // $query->addJoin('inner',' commerce_payment_transaction', 'f7', 'f7.order_id = f6.order_id');
    //$query->fields('f7', ['transaction_id']);
    //$query->addField('f4', 'title', 'product_title');



    //field_data_commerce_order_total
    $query ->condition("cpr.status", 2 );
    $query ->condition("cpr.type", "buildinggreen_license" );

    // $query ->condition("cpr.uid", 57813 );

    return $query;
  }
  public function getCard($uid){
    $query = $this->select("commerce_cardonfile", 'f')
      ->fields('f', ["card_id"]);
    $query ->condition("f.uid", $uid );
    $query ->condition("f.status", 1 );
    $query ->condition("f.instance_default", 1 );
    $query ->orderBy("f.card_id", 'DESC' );
    $query ->range(0, 1 );

    $rows =  $query->execute()->fetchCol();
    if(!empty($rows)){
      return $rows[0];
    }
    return null;
  }
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
  public function prepareRow(Row $row) {



      $last_order_id = $this->getLastActiveOrderId($row->getSourceProperty('license_id'));
      if(empty($last_order_id)){
        return FALSE;
      }

    $row->setSourceProperty('originating_order' ,$this->getOrderId($row->getSourceProperty('license_id')));
    $row->setSourceProperty('unit_price', [
      'currency_code' => 'USD',
      'number' => $this->getOrderAmount($row->getSourceProperty('originating_order')),
    ]);
    $row->setSourceProperty('billing_schedule',
       $this->getBillingCycleType($row->getSourceProperty('product_id'))
    );
    $card = $this->getCard($row->getSourceProperty('uid'));
//var_dump($row->getSourceProperty('product_id'));
//    $tab = [
//      11,
//      77,
//      216,
//      70,
//      220,
//      131,
//      81,
//      218,
//      80
//    ];
//    if(in_array($row->getSourceProperty('product_id'),$tab)  )
//      var_dump($row);
   // var_dump($row->getSourceProperty('license_id'));
    $row->setSourceProperty('payment_method',
      $card
    );
    //$row->setSourceProperty('billing_schedule','billing_monthly');

//    $row->setSourceProperty('next_renewal',
//      $this->fetchCiBilling($this->getLastOrderId($row->getSourceProperty('license_id')),'end')
//    );

   // var_dump($row);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['license_id']['type'] = 'integer';
    $ids['license_id']['alias'] = 'cpr';
    return $ids;
  }

}
