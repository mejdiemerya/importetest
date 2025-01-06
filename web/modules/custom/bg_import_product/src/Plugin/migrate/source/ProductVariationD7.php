<?php
namespace Drupal\buildgreen_migrate\Plugin\migrate\source;


use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\FileTransfer\FileTransfer;
use Drupal\file\Entity\File;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
/**
 * Extract nodes from Drupal 7 database.
 *
 * @MigrateSource(
 * id = "ProductVariationD7",
 * source_module = "migrate_plus"
 * )
 */
class ProductVariationD7 extends SqlBase {

   const ROLES = [
    3 => 'administrator',
      1 => 'anonymous user',
      2 =>  'authenticated user',
      8 =>  'autologin',
      19 =>  'bg_basic',
      16 =>  'bg_communities',
      7 =>  'bg_og_multiuser',
      9 =>  'bg_og_parent',
      4 =>  'bg_premium',
      5 =>  'bg_premium_og',
      6 =>  'bg_trial',
      17 =>  'editor',
      20 =>  'lu_basic',
      15 =>  'lu_guest_expert',
      13 =>  'lu_og_multiuser',
      14 =>  'lu_og_parent',
      10 =>  'lu_premium',
      11 =>  'lu_premium_og',
      12 =>  'lu_trial',
      18 =>  'pre-authorized'
  ];

  public function getProduct( $variationId) {
    $result = $this->select("field_data_field_product", 'f')
      ->fields('f', ["entity_id"])
      ->condition('bundle', array('e_membership_display', 'e_product_display', 'license_notification'), 'IN')
      ->condition('f.field_product_product_id', $variationId, '=');
    $row =  $result->execute()->fetchObject();
    return $row;

  }
  /**
   * {@inheritdoc}
   */
  public function query() {
    $fields = [
      'product_id',
      'sku',
      'title',
      'uid',
      'type',
      'language',
      'status',
      'created',
      'changed',
      'data',
    ];
    $query =  $this->select('commerce_product', 'p')
     // ->condition('type', array('e_membership_display', 'e_product_display', 'license_notification'), 'IN')
      ->fields('p', $fields);
    $query->addJoin('inner','field_data_commerce_price', 'pt', 'p.product_id = pt.entity_id');
    $query->fields('pt', ["commerce_price_amount","commerce_price_currency_code"]);
    $query->addJoin('left','field_data_field_welcome_email_subject', 'f1', 'p.product_id = f1.entity_id');
    $query->fields('f1', ["field_welcome_email_subject"."_value"]);
    $query->addJoin('left','field_data_field_welcome_email_body', 'f2', 'p.product_id = f2.entity_id');
    $query->fields('f2', ["field_welcome_email_body"."_value"]);
    $query->addJoin('left','field_data_field_failed_renewal_subject', 'f3', 'p.product_id = f3.entity_id');
    $query->fields('f3', ["field_failed_renewal_subject"."_value"]);
    $query->addJoin('left','field_data_field_failed_renewal_body', 'f4', 'p.product_id = f4.entity_id');
    $query->fields('f4', ["field_failed_renewal_body"."_value"]);
    $query->addJoin('left','field_data_field_failed_renewal_subject_2', 'f5', 'p.product_id = f5.entity_id');
    $query->fields('f5', ["field_failed_renewal_subject_2"."_value"]);
    $query->addJoin('left','field_data_field_failed_renewal_body_2', 'f6', 'p.product_id = f6.entity_id');
    $query->fields('f6', ["field_failed_renewal_body_2"."_value"]);
    $query->addJoin('left','field_data_field_checkout_complete_message', 'f7', 'p.product_id = f6.entity_id');
    $query->fields('f7', ["field_checkout_complete_message"."_value"]);
    $query->addJoin('left','field_data_bg_commerce_license_role', 'f8', 'p.product_id = f8.entity_id');
    $query->fields('f8', ["bg_commerce_license_role"."_value"]);
    $query->addJoin('left','field_data_bg_commerce_license_trial', 'f9', 'p.product_id = f9.entity_id');
    $query->fields('f9', ["bg_commerce_license_trial_interval","bg_commerce_license_trial_period"]);
    $query->addJoin('left','field_data_bg_commerce_license_trial_price', 'f10', 'p.product_id = f10.entity_id');
    $query->fields('f10', ["bg_commerce_license_trial_price_amount","bg_commerce_license_trial_price_currency_code"]);
    $query->addJoin('left','field_data_field_renewal_number', 'f11', 'p.product_id = f11.entity_id');
    $query->fields('f11', ["field_renewal_number_value"]);
    $query->addJoin('left','field_data_commerce_license_type', 'f12', 'p.product_id = f12.entity_id');
    $query->fields('f12', ["commerce_license_type_value"]);
    $query->addJoin('left','field_data_commerce_license_duration', 'f13', 'p.product_id = f13.entity_id');
    $query->fields('f13', ["commerce_license_duration_value"]);
    $query->addJoin('left','field_data_cl_billing_type', 'f14', 'p.product_id = f14.entity_id');
    $query->fields('f14', ["cl_billing_type_value"]);
    $query->addJoin('left','field_data_cl_billing_cycle_type', 'f15', 'p.product_id = f15.entity_id');
    $query->fields('f15', ["cl_billing_cycle_type_target_id"]);
    $query->addJoin('left','cl_billing_cycle_type', 'f155', 'f15.cl_billing_cycle_type_target_id = f155.billing_cycle_type_id');
    //$query->fields('f155', ["name as cl_billing_cycle_type_name"]);
    $query->addField('f155', 'name', 'cl_billing_cycle_type_name');
    $query->addJoin('left','field_data_bg_commerce_license_description', 'f16', 'p.product_id = f16.entity_id');
    $query->fields('f16', ["bg_commerce_license_description_value"]);

    $query->addJoin('left','field_data_commerce_node_checkout_expire', 'f17', 'p.product_id = f9.entity_id');
    $query->fields('f17', ["commerce_node_checkout_expire_interval","commerce_node_checkout_expire_period"]);

    return $query;

  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'product_id' => $this->t('Product variation ID'),
      'sku' => $this->t('SKU'),
      'title' => $this->t('Title'),
      'uid' => $this->t('uid'),
      'type' => $this->t('Type'),
      'language' => $this->t('Language'),
      'status' => $this->t('Status'),
      'created' => $this->t('Created'),
      'changed' => $this->t('Changes'),
      'data' => $this->t('Data'),
      'commerce_price' => $this->t('Price with amount, currency_code and fraction_digits'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'product_id' => [
        'type' => 'integer',
        'alias' => 'p',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row): bool {

    $product = $this->getProduct($row->getSourceProperty('product_id'));
if(!empty($row->getSourceProperty('bg_commerce_license_role_value'))){
  $row->setSourceProperty('bg_commerce_license_role_value', ProductVariationD7::ROLES[$row->getSourceProperty('bg_commerce_license_role_value')]);
}

    if(!empty($product)){
      $row->setSourceProperty('pid',  $product->entity_id);
    }

//if($row->getSourceProperty('product_id') == 18){
//  var_dump($row);die;
//}
    return parent::prepareRow($row);
  }
}
