<?php

declare(strict_types=1);

namespace Drupal\bg_import_forum\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * This is an example of a simple SQL-based source plugin.
 *
 * Source plugins are classes which deliver source data to the processing
 * pipeline. For SQL sources, the SqlBase class provides most of the
 * functionality needed - for a specific migration, you are required to
 * implement the three simple public methods you see below.
 *
 * This annotation tells Drupal that the name of the MigrateSource plugin
 * configuration references with the source "plugin" key.
 *
 * @MigrateSource(
 *   id = "credit_term"
 * )
 */
final class CreditTerm extends SqlBase
{

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface
  {
    $fields = ['tid','name', 'description', 'weight'];
    $query =  $this->select(' taxonomy_term_data', 'met');
    $query ->fields('met', $fields);
    $query->addJoin('left','field_data_field_credit_requirement_text', 'f1', 'f1.entity_id = met.tid');
    $query ->fields('f1', ["field_credit_requirement_text_value"] );
    $query->addJoin('left','field_data_field_guest_expert', 'f2', 'f2.entity_id = met.tid');
    $query ->fields('f2', ["field_guest_expert_target_id"] );
    $query->addJoin('left','field_data_field_credit_intent', 'f3', 'f3.entity_id = met.tid');
    $query ->fields('f3', ["field_credit_intent_value"] );
    $query->addJoin('left','field_data_field_credit_requirement_points', 'f4', 'f4.entity_id = met.tid');
    $query ->fields('f4', ["field_credit_requirement_points_value"] );
    $query ->condition("met.vid",10 );

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array
  {

    return [
      'name' => $this->t('name'),
      'description' => $this->t('description'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array
  {
    return [
      'tid' => [
        'type' => 'integer',
        // 'alias' is the alias for the table containing 'style' in the query
        // defined above. Optional in this case, but necessary if the same
        // column may occur in multiple tables in a join.
      //  'alias' => 'met',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row)
  {
// Find parents for this row.
    $parents = $this->select('taxonomy_term_hierarchy', 'th')
      ->fields('th', array('parent', 'tid'))
      ->condition('tid', $row->getSourceProperty('tid'))
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('parent', $parents);
    if($row->getSourceProperty('tid') == 5024){
     // var_dump($row);die;
    }
   //
    return parent::prepareRow($row);
  }

}
