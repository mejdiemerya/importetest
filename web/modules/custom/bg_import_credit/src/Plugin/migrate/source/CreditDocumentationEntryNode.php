<?php

declare(strict_types = 1);

namespace Drupal\bg_import_credit\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\file\Entity\File;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for beer content.
 *
 * @MigrateSource(
 *   id = "credit_documentation_entry_node"
 * )
 */
final class CreditDocumentationEntryNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = ['nid','title','vid', 'type', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote'];
    $query =  $this->select(' node', 'n');
    $query ->fields('n', $fields);

    $query ->condition("n.type","credit_documentation_entry" );

    return $query;
  }
  public function getFieldRecordTid($fieldName, $tid){
    $result = $this->select("field_data_{$fieldName}", 'f')
      ->fields('f', ["{$fieldName}_tid"])
      ->condition('f.bundle', 'credit_documentation_entry', '=')
      ->condition('f.entity_id', $tid, '=');

    $rows =  $result->execute()->fetchCol();
    if(!empty($rows)){
      return $rows;
    }
    return null;
  }

  public function getFile($id){
    $query = $this->select("field_data_field_documentation_file", 'u');
    $query->addJoin('inner','file_managed','fm','u.field_documentation_file_fid = fm.fid');
    $query ->fields('fm', ["uri","fid"]);
    $query ->condition('u.entity_id', $id, '=');
    $row =  $query->execute()->fetchObject();
    return $row;
  }
  public function createFile($id, $uid) {
    $file_system = \Drupal::service('file_system');
    $contentFile = $this->getFile($id);
    if(!$contentFile || empty($contentFile) || empty($contentFile->uri)){
      return -1;
    }

    $file = File::create([
      'filename' => basename($contentFile->uri),
      'uri' => 'public://credit_documentation/' . basename($contentFile->uri),
      'status' => 1,
      'uid' => $uid,
    ]);

    $file->save();
    return $file->id();


  }
  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'nid' => $this->t('node ID'),
      'title' => $this->t('titler'),
      'status' => $this->t('titler'),
      'body' => $this->t('Full description  r'),
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

    $docType = $this->getFieldRecordTid('field_documentation_type',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_documentation_type_tid', $docType);



    $fileId = $this->createFile($row->getSourceProperty('nid'),$row->getSourceProperty('uid'));

    if($fileId != -1){
      $row->setSourceProperty('myfile', $fileId);
      //$row->setSourceProperty('picture', $fileId);
    }else{
      $row->setSourceProperty('myfile', null);
    }

    return parent::prepareRow($row);
  }

}
