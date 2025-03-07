<?php

declare(strict_types = 1);

namespace Drupal\bg_import_credit\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\file\Entity\File;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for   content.
 *
 * @MigrateSource(
 *   id = "credit_checklist_node"
 * )
 */
final class CreditChecklistNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = ['nid','title','vid', 'type', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote'];
    $query =  $this->select(' node', 'n');
    $query ->fields('n', $fields);
     $query->addJoin('left','field_data_body', 'f5', 'f5.entity_id = n.nid');
    $query ->fields('f5', ["body_value","body_format","body_summary"]);
    $query ->condition("n.type","credit_checklist" );

    return $query;
  }
  public function getFieldRecordTid($fieldName, $tid){
    $result = $this->select("field_data_{$fieldName}", 'f')
      ->fields('f', ["{$fieldName}_tid"])
      ->condition('f.bundle', 'credit_checklist', '=')
      ->condition('f.entity_id', $tid, '=');

    $rows =  $result->execute()->fetchCol();
    if(!empty($rows)){
      return $rows;
    }
    return null;
  }


  public function getFile($id, $field){
    $query = $this->select("field_data_field_{$field}", 'u');
    $query->addJoin('inner','file_managed','fm','u.field_'.$field.'_fid = fm.fid');
    $query ->fields('fm', ["uri" ]);
    $query ->condition('u.entity_id', $id, '=');
    $row =  $query->execute()->fetchAll();
    return $row;
  }
  public function createFile($id, $uid, $field) {
    $tabfile = [];
    $file_system = \Drupal::service('file_system');
    $contentFile = $this->getFile($id, $field);

    if(!$contentFile || empty($contentFile)  ){
      return -1;
    }

foreach ($contentFile as $row) {
  $file = File::create([
    'filename' => basename($row['uri']),
    'uri' => 'public://credit/checklists/' . basename($row['uri']),
    'status' => 1,
    'uid' => $uid,
  ]);
  $file->save();
  $tabfile [] = $file->id();
}


    return $tabfile;


  }
  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'nid' => $this->t('node ID'),
      'title' => $this->t('title'),
      'status' => $this->t('status'),
      'body' => $this->t('Full description'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row): bool {

    $credits = $this->getFieldRecordTid('field_credits',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_credits_tid', $credits);
    $creditsLabel = $this->getFieldRecordTid('field_credit_labels',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_credit_labels_tid', $creditsLabel);
    $field_project_phase = $this->getFieldRecordTid('field_project_phase',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_project_phase_tid', $field_project_phase);
    $field_credit_tip_type = $this->getFieldRecordTid('field_credit_tip_type',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_credit_tip_type_tid', $field_credit_tip_type);
    $field_who_does_it = $this->getFieldRecordTid('field_who_does_it',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_who_does_it_tid', $field_who_does_it);


    $myfilelandscape = $this->createFile($row->getSourceProperty('nid'),$row->getSourceProperty('uid'),'image_landscape');

    if($myfilelandscape != -1){
      $row->setSourceProperty('myfilelandscape', $myfilelandscape);
      //$row->setSourceProperty('picture', $fileId);
    }else{
      $row->setSourceProperty('myfilelandscape', null);
    }

    $myfileportrait = $this->createFile($row->getSourceProperty('nid'),$row->getSourceProperty('uid'),'image_portrait');
    if($myfileportrait != -1){
      $row->setSourceProperty('myfileportrait', $myfileportrait);
    }else{
      $row->setSourceProperty('myfileportrait', null);
    }

    return parent::prepareRow($row);
  }

}
