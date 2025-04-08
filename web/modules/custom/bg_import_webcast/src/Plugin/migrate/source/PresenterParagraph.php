<?php

declare(strict_types=1);

namespace Drupal\bg_import_webcast\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Drupal\paragraphs\Plugin\migrate\source\d7\FieldCollectionItem;

/**
 * Source plugin for webcast nodes.
 *
 * @MigrateSource(
 *   id = "paragraph_presenter"
 * )
 */
final class PresenterParagraph extends FieldCollectionItem {
  public function getFile($id){
    $query = $this->select("field_data_field_presenter_photo", 'u');
    $query->addJoin('inner','file_managed','fm','u.field_presenter_photo_fid = fm.fid');
    $query ->fields('fm', ["uri" ]);
    $query ->condition('u.entity_id', $id, '=');
    $row =  $query->execute()->fetchAll();
    return $row;
  }
  public function getbio($id) {
    $query = $this->select("field_data_field_presenter_bio", 'u')
      ->fields('u', ['field_presenter_bio_value'])
      ->condition('u.entity_id', $id, '=');

    $result = $query->execute()->fetchAll();
    return $result;
  }

  public function createFile($id) {
    $tabfile = [];
    $file_system = \Drupal::service('file_system');
    $contentFile = $this->getFile($id);

    if(!$contentFile || empty($contentFile)  ){
      return -1;
    }

    foreach ($contentFile as $row) {
      $file = File::create([
        'filename' => basename($row['uri']),
        'uri' => 'public://headshots/' . basename($row['uri']),
        'status' => 1,
      ]);
      $file->save();
      $tabfile [] = $file->id();
    }


    return $tabfile;


  }

  public function prepareRow(Row $row): bool {
    $nid = (int) $row->getSourceProperty('item_id');
    $myfileportrait = $this->createFile($nid);

    if (is_array($myfileportrait) && !empty($myfileportrait[0])) {

        $row->setSourceProperty('field_presenter_photo_target_id', $myfileportrait[0]);
    }


    $bio = $this->getbio($nid);
    if (!empty($bio)) {
      $row->setSourceProperty('field_presenter_bio_value', $bio[0]['field_presenter_bio_value']);
    }


    return parent::prepareRow($row);
  }


}
