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
 *   id = "bg_og_compus_wide"
 * )
 */
final class BgOgCompusMembership extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {

    $query =  $this->select(' og_membership', 'n');
    $query ->fields('n');
    $query->addJoin('inner','node', 'f1', 'f1.nid = n.gid and n.etid != f1.uid');
    $query ->fields('f1', ["nid"] );
    $query->addJoin('inner','domain_access', 'f2', 'f2.nid = f1.nid');
    $query ->condition("f2.gid","2" );
    $query ->condition("f1.type","campus_wide_account" );
    $query ->condition("n.entity_type","user" );
    //echo $query->__toString();
    //$query ->condition("n.gid","69679" );
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'id' => $this->t(' ID'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'id' => [
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
