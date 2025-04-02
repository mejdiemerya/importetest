<?php

declare(strict_types = 1);

namespace Drupal\bg_import\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for beer comments.
 *
 * @MigrateSource(
 *   id = "blog_comment"
 * )
 */
final class BlogComment extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = [
      'cid',
      'pid',
      'name',
      'mail',
      'nid',
      'uid',
      'subject',
      'language',
      'hostname',
      'created',
      'changed',
      'status',
      'thread',
      'homepage',
    ];
     $query =  $this->select('comment', 'mec');
     $query ->fields('mec', $fields);
    $query->addJoin('inner','field_data_comment_body', 'f1', 'f1.entity_id = mec.cid');
    $query ->fields('f1', ["comment_body_value","comment_body_format"] );
    $query ->condition("f1.bundle","comment_node_blog" );
   // $query ->condition("mec.cid","14163" );


    return $query;

  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'cid' => $this->t('Comment ID'),
      'pid' => $this->t('Parent comment ID in case of comment replies'),
      'name' => $this->t('Comment name (if anon)'),
      'mail' => $this->t('Comment email (if anon)'),
      'uid' => $this->t('Account ID (if any)'),
      'entity_id' => $this->t('  ID that is being commented upon'),
      'subject' => $this->t('Comment subject'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'cid' => [
        'type' => 'integer',
        'alias' => 'mec',
      ],
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row)
  {

    return parent::prepareRow($row);
  }
}
