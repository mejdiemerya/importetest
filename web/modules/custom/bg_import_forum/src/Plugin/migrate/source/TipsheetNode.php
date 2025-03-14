<?php

declare(strict_types = 1);

namespace Drupal\bg_import_forum\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for beer content.
 *
 * @MigrateSource(
 *   id = "tipsheet_node"
 * )
 */
final class TipsheetNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = ['nid','title','vid', 'type', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote'];
    $query =  $this->select(' node', 'n');
    $query ->fields('n', $fields);
//    $query->addJoin('left','field_data_field_credits', 'f1', 'f1.entity_id = n.nid');
//    $query ->fields('f1', ["field_credits_tid"] );
    $query->addJoin('left','field_data_field_guest_expert', 'f2', 'f2.entity_id = n.nid');
    $query ->fields('f2', ["field_guest_expert_target_id"] );

    $query->addJoin('left','field_data_body', 'f3', 'f3.entity_id = n.nid');
    $query ->fields('f3', ["body_value","body_format","body_summary"] );

    $query ->condition("n.type","tipsheet" );

    return $query;
  }

  public function getFieldRecordTid($fieldName, $tid){
    $result = $this->select("field_data_{$fieldName}", 'f')
      ->fields('f', ["{$fieldName}_tid"])
      ->condition('f.bundle', 'tipsheet', '=')
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
    return parent::prepareRow($row);
  }

}
