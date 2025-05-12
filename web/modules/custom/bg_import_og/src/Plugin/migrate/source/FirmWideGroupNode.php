<?php

declare(strict_types = 1);

namespace Drupal\bg_import_og\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for beer content.
 *
 * @MigrateSource(
 *   id = "firmwide_node"
 * )
 */
final class FirmWideGroupNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = ['nid','title','vid', 'type', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote'];
    $query =  $this->select(' node', 'n');
    $query ->fields('n', $fields);
    $query ->condition("n.type","firm_wide_account" );
    $query->addJoin('inner','domain_access', 'f1', 'f1.nid = n.nid');
    $query ->condition("f1.gid","2" );
    //domain_access

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'nid' => $this->t('node ID'),
      'title' => $this->t('titler'),
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

    return parent::prepareRow($row);
  }

}
