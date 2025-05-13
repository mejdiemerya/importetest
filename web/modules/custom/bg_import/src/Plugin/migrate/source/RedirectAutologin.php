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
 *   id = "redirect_autologin"
 * )
 */
final class RedirectAutologin extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {

    $query =  $this->select('redirect', 'n');
    $query ->fields('n');



    $query ->condition("n.redirect","autologin/"."%" , 'LIKE' );
    //$query ->condition("n.rid", 12000 );

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'rid' => $this->t('node ID'),

    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'rid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row): bool {

    $redirect =  $row->getSourceProperty('redirect');
    $row->setSourceProperty('redirect', "internal:/".$redirect);

    return parent::prepareRow($row);
  }

}
