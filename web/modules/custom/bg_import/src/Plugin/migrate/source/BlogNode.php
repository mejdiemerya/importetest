<?php

declare(strict_types = 1);

namespace Drupal\bg_import\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\file\Entity\File;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for blog content.
 *
 * @MigrateSource(
 *   id = "blog_node"
 * )
 */
final class BlogNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = ['nid','title','vid', 'type', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote'];
    $query =  $this->select(' node', 'n');
    $query ->fields('n', $fields);
    $query->addJoin('left','field_data_field_author', 'f1', 'f1.entity_id = n.nid');
    $query ->fields('f1', ["field_author_target_id"] );

    $query->addJoin('left','field_data_field_dek', 'f2', 'f2.entity_id = n.nid');
    $query ->fields('f2', ["field_dek_value"] );


    $query->addJoin('left','field_data_body', 'f5', 'f5.entity_id = n.nid');
    $query ->fields('f5', ["body_value","body_format"] );

    $query ->condition("n.type","blog" );

    return $query;
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
        'uri' => 'public://articles/' . basename($row['uri']),
        'status' => 1,
        'uid' => $uid,
      ]);
      $file->save();
      $tabfile [] = $file->id();
    }
    return $tabfile;
  }

  public function getFile($id, $field){
    $query = $this->select("field_data_field_{$field}", 'u');
    $query->addJoin('inner','file_managed','fm','u.field_'.$field.'_fid = fm.fid');
    $query ->fields('fm', ["uri" ]);
    $query ->condition('u.entity_id', $id, '=');
    $query ->range(0, 1);
    $query ->orderBy('u.delta');
    $row =  $query->execute()->fetchAll();
    return $row;
  }


  public function getFieldRecordFile($nid){
    $query = $this->select("field_data_field_image_landscape", 'a');

    $query->addJoin('left','field_data_field_image_credit_url','b','b.entity_id = a.field_image_landscape_fid');
    $query ->fields('b', ["field_image_credit_url_value" ]);
    $query->addJoin('left','field_data_field_image_caption','c','c.entity_id = a.field_image_landscape_fid');
    $query ->fields('c', ["field_image_caption_value" ]);
    $query->addJoin('left','field_data_field_image_credit','d','d.entity_id = a.field_image_landscape_fid');
    $query ->fields('d', ["field_image_credit_value" ]);
    $query->addJoin('left','field_data_field_file_image_alt_text','e','e.entity_id = a.field_image_landscape_fid');
    $query ->fields('e', ["field_file_image_alt_text_value" ]);

    $query ->condition('a.entity_id', $nid, '=');
    $row =  $query->execute()->fetchAssoc();
    return $row;
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

    $myfilelandscape = $this->createFile($row->getSourceProperty('nid'),$row->getSourceProperty('uid'),'image_landscape');
    if($myfilelandscape != -1){
      $row->setSourceProperty('myfilelandscape', $myfilelandscape[0]);
    }else{
      $row->setSourceProperty('myfilelandscape', null);
    }
    $recordsFile = $this->getFieldRecordFile($row->getSourceProperty('nid'));
    $row->setSourceProperty('field_image_caption_value', !empty($recordsFile['field_image_caption_value']) ? $recordsFile['field_image_caption_value'] : NULL);
    $row->setSourceProperty('field_image_credit_url_value', !empty($recordsFile['field_image_credit_url_value']) ? $recordsFile['field_image_credit_url_value'] : NULL);
    $row->setSourceProperty('field_image_credit_value', !empty($recordsFile['field_image_credit_value']) ? $recordsFile['field_image_credit_value'] : NULL);
    $row->setSourceProperty('field_file_image_alt_text_value', !empty($recordsFile['field_file_image_alt_text_value']) ? $recordsFile['field_file_image_alt_text_value'] : NULL);
    return parent::prepareRow($row);
  }

}
