<?php
namespace Drupal\bg_import\Plugin\migrate\source;


use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\FileTransfer\FileTransfer;
use Drupal\file\Entity\File;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 * id = "UserD7",
 * source_module = "migrate_plus"
 * )
 */
class UserD7 extends SqlBase {
  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('users', 'u')
      ->fields('u', array_keys($this->baseFields()))
      ->condition('uid', 0, '>')->orderBy('uid', 'ASC');
  }
  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['field_first_name'] = $this->t('First Name');
    $fields['field_last_name'] = $this->t('Last Name');
    $fields['field_accreditations'] = $this->t('Accreditations');
    $fields['field_bio'] = $this->t('Bio');
    $fields['field_organization'] = $this->t('Organization');
    $fields['field_title'] = $this->t('Title');
    $fields['field_website'] = $this->t('Web site');
    return $fields;
  }
  public function getFieldRecord($fieldName, $uid){
    $result = $this->select("field_data_{$fieldName}", 'f')
      ->fields('f', ["{$fieldName}_value"])
      ->condition('f.bundle', 'user', '=')
      ->condition('f.entity_id', $uid, '=');
    $row =  $result->execute()->fetchObject();
    if(!empty($row->{$fieldName."_value"})){
      return $row->{$fieldName."_value"};
    }
    return null;
}
  public function getPicture($uid){
    $query = $this->select("users", 'u');
      //$query->fields('u', ["picture"]);
      $query->addJoin('inner','file_managed','fm','u.picture = fm.fid');
     $query ->fields('fm', ["uri","fid"]);
     $query ->condition('u.uid', $uid, '=');
    $row =  $query->execute()->fetchObject();


    return $row;
}

public function createIfNotExist($filepath) {


}
public function copyPicture($uid) {
  $file_system = \Drupal::service('file_system');
  $userPicture = $this->getPicture($uid);
  if(!$userPicture || empty($userPicture)){
    return -1;
  }
  if(!empty($userPicture->uri) && file_exists($file_system->realpath($userPicture->uri))){
    //$userPicture->uri =   $file_system->copy($imgFileToCopy, "public://user_pictures/img_user_none".time().".png");
//    var_dump($userPicture->uri);
//    var_dump($uid);
    $userPicture->uri =   $file_system->copy($userPicture->uri, 'public://user_pictures/' . basename($userPicture->uri));
    $file = File::create([
      'filename' => basename($userPicture->uri),
      'uri' => 'public://user_pictures/' . basename($userPicture->uri),
      'status' => 1,
      'uid' => $uid,
    ]);
    return  $file->save();
  }
  return -1;
}
public function createPicture($uid) {
  $file_system = \Drupal::service('file_system');
  $userPicture = $this->getPicture($uid);
  if(!$userPicture || empty($userPicture)){
    return -1;
  }
  ////$imgFileToCopy = "public://"
    //$userPicture->uri =   $file_system->copy($imgFileToCopy, "public://user_pictures/img_user_none".time().".png");
//    var_dump($userPicture->uri);
//    var_dump($uid);
    //$userPicture->uri =   $file_system->copy($userPicture->uri, 'public://user_pictures/' . basename($userPicture->uri));
    $file = File::create([
      'filename' => basename($userPicture->uri),
      'uri' => 'public://pictures/' . basename($userPicture->uri),
      'status' => 1,
      'uid' => $uid,
    ]);

      $file->save();
      return $file->id();


}
  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $uid = $row->getSourceProperty('uid');

    $field_first_name = $this->getFieldRecord('field_first_name', $uid);
    if(!empty($field_first_name)){
      $row->setSourceProperty('field_first_name', $field_first_name);
    }
    $field_last_name = $this->getFieldRecord('field_last_name', $uid);
    if(!empty($field_last_name)){
      $row->setSourceProperty('field_last_name', $field_last_name);
    }
    $field_accreditations = $this->getFieldRecord('field_accreditations', $uid);
    if(!empty($field_accreditations)){
      $row->setSourceProperty('field_accreditations', $field_accreditations);
    }

    $field_bio = $this->getFieldRecord('field_bio', $uid);
    if(!empty($field_bio)){
      $row->setSourceProperty('field_bio', $field_bio);
    }
    $field_organization = $this->getFieldRecord('field_organization', $uid);

    if(!empty($field_organization)){
      $row->setSourceProperty('field_organization', $field_organization);
    }
    $field_title = $this->getFieldRecord('field_title', $uid);
    if(!empty($field_title)){
      $row->setSourceProperty('field_title', $field_title);
    }
    $field_website = $this->getFieldRecord('field_website', $uid);
    if(!empty($field_website)){
      $row->setSourceProperty('field_website', $field_website);
    }

    $field_bpi_number = $this->getFieldRecord('field_bpi_number', $uid);
    if(!empty($field_bpi_number)){
      $row->setSourceProperty('field_bpi_number', $field_bpi_number);
    }

    $field_bulletin_opt_in = $this->getFieldRecord('field_bulletin_opt_in', $uid);
    if(!empty($field_bulletin_opt_in)){
      $row->setSourceProperty('field_bulletin_opt_in', $field_bulletin_opt_in);
    }
    $field_ilfi_member = $this->getFieldRecord('field_ilfi_member', $uid);
    if(!empty($field_ilfi_member)){
      $row->setSourceProperty('field_ilfi_member', $field_ilfi_member);
    }

    $field_nari_member = $this->getFieldRecord('field_nari_member', $uid);
    if(!empty($field_nari_member)){
      $row->setSourceProperty('field_nari_member', $field_nari_member);
    }

    $field_gbci_email = $this->getFieldRecord('field_gbci_email', $uid);
    if(!empty($field_gbci_email)){
      $row->setSourceProperty('field_gbci_email', $field_gbci_email);
    }

    $field_dp_firm_token = $this->getFieldRecord('field_dp_firm_token', $uid);
    if(!empty($field_dp_firm_token)){
      $row->setSourceProperty('field_dp_firm_token', $field_dp_firm_token);
    }
    $field_gbci_number = $this->getFieldRecord('field_gbci_number', $uid);
    if(!empty($field_gbci_number)){
      $row->setSourceProperty('field_gbci_number', $field_gbci_number);
    }
    $field_expert_notification_last = $this->getFieldRecord('field_expert_notification_last', $uid);
    if(!empty($field_expert_notification_last)){
      $row->setSourceProperty('field_expert_notification_last', $field_expert_notification_last);
    }

    $field_hide_email = $this->getFieldRecord('field_hide_email', $uid);
    if(!empty($field_hide_email)){
      $row->setSourceProperty('field_hide_email', $field_hide_email);
    }

    $field_last_mailchimp_sync_date = $this->getFieldRecord('field_last_mailchimp_sync_date', $uid);

    if(!empty($field_last_mailchimp_sync_date)){
      $date = DrupalDateTime::createFromTimestamp(strtotime($field_last_mailchimp_sync_date), 'UTC');
      $row->setSourceProperty('field_last_mailchimp_sync_date', $date->format("Y-m-d\TH:i:s"));
    }
    $field_last_reminded = $this->getFieldRecord('field_last_reminded', $uid);
    if(!empty($field_last_reminded)){
      $row->setSourceProperty('field_last_reminded', $field_last_reminded);
    }
    //if($uid == 209)var_dump($field_last_reminded);
    $field_region = $this->getFieldRecord('field_region', $uid);
    if(!empty($field_region)){
      $row->setSourceProperty('field_region', $field_region);
    }

    $field_regional_coordinator = $this->getFieldRecord('field_regional_coordinator', $uid);
    if(!empty($field_regional_coordinator)){
      $row->setSourceProperty('field_regional_coordinator', $field_regional_coordinator);
    }
    $field_sub_notification_last = $this->getFieldRecord('field_sub_notification_last', $uid);
    if(!empty($field_sub_notification_last)){
      $row->setSourceProperty('field_sub_notification_last', $field_sub_notification_last);
    }

    $field_trial_taken = $this->getFieldRecord('field_trial_taken', $uid);
    if(!empty($field_trial_taken)){
      $row->setSourceProperty('field_trial_taken', $field_trial_taken);
    }

    $field_trial_taken_lu = $this->getFieldRecord('field_trial_taken_lu', $uid);
    if(!empty($field_trial_taken_lu)){
      $row->setSourceProperty('field_trial_taken_lu', $field_trial_taken_lu);
    }

    $field_dp_token = $this->getFieldRecord('field_dp_token', $uid);
    if(!empty($field_dp_token)){
      $row->setSourceProperty('field_dp_token', $field_dp_token);
    }



    //$fileId = $this->copyPicture($uid);
    $fileId = $this->createPicture($uid);
    if($fileId != -1){
     // var_dump($uid.'--'.$fileId);
      $row->setSourceProperty('picture', $fileId);
      //$row->setSourceProperty('picture', $fileId);
    }
    // on importe les roles
    $query = $this->select('users_roles', 'r');
    $query->fields('r', ['rid']);
    $query->condition('r.uid', $uid, '=');
    $result = $query->execute();
    if(!empty($result)){
      $keys = $result->fetchCol();
      if(!empty($keys)){
        $row->setSourceProperty('roles',   ($keys));
      }
    }



    return parent::prepareRow($row);
  }
  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
        'alias' => 'u',
      ],
    ];
  }
  /**
   * User base fields.
   *
   * @return array
   * Base fields array.
   */
  protected function baseFields() {

    $fields =  [
      'uid' => $this->t('User ID'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'status' => $this->t('Status'),
      'language' => $this->t('Language'),
      'signature' => $this->t('Signature'),
      'signature_format' => $this->t('Signature format'),
      'created' => $this->t('Registered timestamp'),
      'access' => $this->t('Last access timestamp'),
      'login' => $this->t('Last login timestamp'),
      'init' => $this->t('Init'),
    ];
    return $fields;
  }
  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }
  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'user';
  }
}
