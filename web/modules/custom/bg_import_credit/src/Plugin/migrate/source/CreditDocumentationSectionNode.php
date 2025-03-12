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
 *   id = "credit_documentation_section_node"
 * )
 */
final class CreditDocumentationSectionNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = ['nid','title','vid', 'type', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote'];
    $query =  $this->select(' node', 'n');
    $query ->fields('n', $fields);
     $query->addJoin('left','field_data_body', 'f5', 'f5.entity_id = n.nid');
    $query ->fields('f5', ["body_value","body_format","body_summary"]);
    $query ->condition("n.type","credit_documentation_section" );

    return $query;
  }
  public function getFieldRecordTid($fieldName, $tid){
    $result = $this->select("field_data_{$fieldName}", 'f')
      ->fields('f', ["{$fieldName}_tid"])
      ->condition('f.bundle', 'credit_documentation_section', '=')
      ->condition('f.entity_id', $tid, '=');

    $rows =  $result->execute()->fetchCol();
    if(!empty($rows)){
      return $rows;
    }
    return null;
  }
  public function getFieldRecordTargetId($fieldName, $tid){
    $result = $this->select("field_data_{$fieldName}", 'f')
      ->fields('f', ["{$fieldName}_target_id"])
      ->condition('f.bundle', 'credit_documentation_section', '=')
      ->condition('f.entity_id', $tid, '=');

    $rows =  $result->execute()->fetchCol();
    if(!empty($rows)){
      return $rows;
    }
    return null;
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

    $credits = $this->getFieldRecordTid('field_credits',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_credits_tid', $credits);
    $creditsLabel = $this->getFieldRecordTid('field_credit_labels',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_credit_labels_tid', $creditsLabel);
    $creditsLabel = $this->getFieldRecordTargetId('field_documents',$row->getSourceProperty('nid'));
    $row->setSourceProperty('field_documents_target_id', $creditsLabel);

    return parent::prepareRow($row);
  }

}
