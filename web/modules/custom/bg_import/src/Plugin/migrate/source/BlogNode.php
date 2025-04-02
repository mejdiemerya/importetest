<?php

declare(strict_types = 1);

namespace Drupal\bg_import\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
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

    return parent::prepareRow($row);
  }

}
