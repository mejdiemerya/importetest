<?php

namespace Drupal\bg_import_orders\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal 7   source from database.
 *
 * @MigrateSource(
 *   id = "bg_license",
 *   source_module = "commerce_license"
 * )
 */
class BgLicense extends FieldableEntity {

  /**
   * The join options between commerce_customer_profile and its revision table.
   */
  const JOIN = 'cp.revision_id = cpr.revision_id';

  //
  public function getOrderId($license_id) {
    $query = $this->select('field_data_cl_billing_license', 'cpr')
      ->fields('cpr');
    $query ->condition("cpr.cl_billing_license_target_id", $license_id );

    $query->addJoin('left','commerce_line_item', 'f4', 'f4.line_item_id = cpr.entity_id');
    $query->addField('f4', 'order_id', 'originating_order');
    $query->orderBy('f4.order_id' ,'ASC');
    $query->range(0,1);
     $result = $query->execute()->fetchObject();
     if(!empty($result->originating_order))
     return $result->originating_order;

     return null;
  }
  /**
   * {@inheritdoc}
   */
  //
  public function query() {
    $query = $this->select('commerce_license', 'cpr')
      ->fields('cpr');
     //$query ->condition("cpr.uid", 57813 );
    $query->addJoin('left','field_data_bg_commerce_license_role', 'f1', 'f1.entity_id = cpr.product_id');
    $query ->fields('f1', ["bg_commerce_license_role_value"] );
    $query->addJoin('left','role', 'f2', 'f2.rid = f1.bg_commerce_license_role_value');
    $query->addField('f2', 'name', 'role_name');
//    $query->addJoin('left','field_data_cl_billing_license', 'f3', 'f3.cl_billing_license_target_id = cpr.license_id');
//    $query->addField('f3', 'entity_id', 'f3entity_id');
//    $query->addJoin('left','commerce_line_item', 'f4', 'f4.line_item_id = f3.entity_id');
//    $query->addField('f4', 'order_id', 'originating_order');




    return $query;
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
    \Drupal::logger('bg_subscription')->info('Source IDs: @ids', ['@ids' => print_r($row->getSource(), TRUE)]);

    $row->setSourceProperty('originating_order' ,$this->getOrderId($row->getSourceProperty('license_id')));
//var_dump($row);
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
