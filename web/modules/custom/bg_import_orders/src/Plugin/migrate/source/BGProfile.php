<?php

namespace Drupal\bg_import_orders\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal 7 commerce_customer_profile source from database.
 *
 * @MigrateSource(
 *   id = "bg_profile",
 *   source_module = "commerce_customer"
 * )
 */
class BgProfile extends FieldableEntity {

  /**
   * The join options between commerce_customer_profile and its revision table.
   */
  //const JOIN = 'cp.revision_id = cpr.revision_id';
  const JOIN = 'f1.entity_id = cp.profile_id';

  /**
   * {@inheritdoc}
   */
  //
  public function query() {
    $query = $this->select('commerce_customer_profile', 'cp')
      ->fields('cp');
    $query->innerJoin('field_data_commerce_customer_address', 'f1', static::JOIN);
    $query->fields('f1');
   // $query->addField('cpr', 'status', 'revision_status');
   // $query->addField('cpr', 'data', 'revision_data');
    $query->addField('cp', 'revision_id', 'myrevisionid');
   // $query ->condition("cp.uid", 57813 );
//echo $query->__toString();



   /* if ($this->getDatabase()->schema()->tableExists('commerce_addressbook_defaults')) {
      $query->leftJoin('commerce_addressbook_defaults', 'cad', 'cp.profile_id = cad.profile_id AND cp.uid = cad.uid AND cp.type = cad.type');
      $query->addField('cad', 'type', 'cad_type');
    }
    else {
      // If the currency column does not exist, add it as an expression to
      // normalize the query results.
      $query->addExpression(':cad', 'cad_type', [':cad' => FALSE]);
    }

    if (isset($this->configuration['profile_type'])) {
      $types = is_array($this->configuration['profile_type']) ? $this->configuration['profile_type'] : [$this->configuration['profile_type']];
      $query->condition('cp.type', $types, 'IN');
    }*/
//    print_r($this->configuration['profile_type']);
  //  echo $query->__toString();
    return $query;
  }  public function query1() {
    $query = $this->select('commerce_customer_profile_revision', 'cpr')
      ->fields('cpr');
    $query->innerJoin('commerce_customer_profile', 'cp', static::JOIN);
    $query->fields('cp');
    $query->innerJoin('field_data_commerce_customer_address', 'f1', static::JOIN);
    $query->fields('f1');
    $query->addField('cpr', 'status', 'revision_status');
    $query->addField('cpr', 'data', 'revision_data');
    $query->addField('cpr', 'revision_id', 'myrevisionid');
   // $query ->condition("cp.uid", 57813 );
//echo $query->__toString();


    /** @var \Drupal\Core\Database\Schema $db */
   /* if ($this->getDatabase()->schema()->tableExists('commerce_addressbook_defaults')) {
      $query->leftJoin('commerce_addressbook_defaults', 'cad', 'cp.profile_id = cad.profile_id AND cp.uid = cad.uid AND cp.type = cad.type');
      $query->addField('cad', 'type', 'cad_type');
    }
    else {
      // If the currency column does not exist, add it as an expression to
      // normalize the query results.
      $query->addExpression(':cad', 'cad_type', [':cad' => FALSE]);
    }

    if (isset($this->configuration['profile_type'])) {
      $types = is_array($this->configuration['profile_type']) ? $this->configuration['profile_type'] : [$this->configuration['profile_type']];
      $query->condition('cp.type', $types, 'IN');
    }*/
//    print_r($this->configuration['profile_type']);
    //echo $query->__toString();
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'profile_id' => $this->t('Profile ID'),
      'type' => $this->t('Type'),
      'uid' => $this->t('Owner'),
      'status' => $this->t('Status'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'data' => $this->t('Data blob'),
      'cad_type' => $this->t('Type, if matching entry in defaults table'),
      'revision_id' => $this->t('The primary identifier for this version.'),
      'revision_uid' => $this->t('The primary identifier for this revision.'),
      'log' => $this->t('Revision Log message'),
      'revision_timestamp' => $this->t('Revision timestamp'),
      'revision_data' => $this->t('The revision data'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $row->setSourceProperty('data', unserialize($row->getSourceProperty('data') ?: ''));
    $row->setSourceProperty('revision_data', unserialize($row->getSourceProperty('revision_data') ?: ''));

    $profile_id = $row->getSourceProperty('profile_id');
    $revision_id = $row->getSourceProperty('revision_id');
    // Get Field API field values.
    foreach (array_keys($this->getFields('commerce_customer_profile', $row->getSourceProperty('type'))) as $field) {
      $row->setSourceProperty($field, $this->getFieldValues('commerce_customer_profile', $field, $profile_id, $revision_id));
    }
    $profile_address['langcode'] = 'en';
    $profile_address['country_code'] = $row->getSourceProperty('commerce_customer_address_country');

    $profile_address['administrative_area'] = $row->getSourceProperty('commerce_customer_address_administrative_area');
    $profile_address['locality'] = $row->getSourceProperty('commerce_customer_address_locality');
    $profile_address['dependent_locality'] = $row->getSourceProperty('commerce_customer_address_dependent_locality');
    $profile_address['postal_code'] = $row->getSourceProperty('commerce_customer_address_postal_code');
    $profile_address['address_line1'] = $row->getSourceProperty('commerce_customer_address_thoroughfare');
    $profile_address['address_line2'] = $row->getSourceProperty('commerce_customer_address_premise');
    $profile_address['address_line3'] = $row->getSourceProperty('commerce_customer_address_sub_premise');
    $profile_address['organization'] = $row->getSourceProperty('commerce_customer_address_organisation_name');
    $profile_address['additional_name'] = $row->getSourceProperty('commerce_customer_address_name_line');
    $profile_address['given_name'] = $row->getSourceProperty('commerce_customer_address_first_name');
    $profile_address['family_name'] = $row->getSourceProperty('commerce_customer_address_last_name');
    $row->setSourceProperty('profile_address', $profile_address);

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['profile_id']['type'] = 'integer';
    $ids['profile_id']['alias'] = 'cp';
    return $ids;
  }

}
