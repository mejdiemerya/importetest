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
 *   id = "author_node"
 * )
 */
final class AuthorNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = ['nid','title','vid', 'type', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote'];
    $query =  $this->select(' node', 'n');
    $query ->fields('n', $fields);


    $query->addJoin('left','field_data_field_bio', 'f2', 'f2.entity_id = n.nid');
    $query ->fields('f2', ["field_bio_value"] );



    $query ->condition("n.type","author" );

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
