<?php

declare(strict_types=1);

namespace Drupal\bg_import_webcast\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\media\Entity\Media;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for webcast nodes.
 *
 * @MigrateSource(
 *   id = "webcast_node"
 * )
 */
final class WebcastNode extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $fields = ['nid','title','vid', 'type', 'language', 'title', 'uid', 'status', 'created', 'changed', 'promote'];
    $query =  $this->select(' node', 'n');
    $query ->fields('n', $fields);
     $query->addJoin('left','field_data_body', 'f5', 'f5.entity_id = n.nid');
    $query ->fields('f5', ["body_value","body_format","body_summary"])
    ->condition("n.type","webcast" );

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'nid' => $this->t('node ID'),
      'title' => $this->t('title'),
      'status' => $this->t('status'),
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
  protected function loadParagraphData(int $nid): array {
    try {
      // Vérifiez d'abord si la table existe
      $table_name = 'field_data_field_presenter';
      if (!$this->getDatabase()->schema()->tableExists($table_name)) {
        // Essayez avec un autre nom de table possible
        $table_name = 'node__field_presenter';
        if (!$this->getDatabase()->schema()->tableExists($table_name)) {
          return [];
        }
      }

      $query = $this->getDatabase()->select($table_name, 'fp')
        ->fields('fp', [
          'field_presenter_value',
          'field_presenter_revision_id',
          // Add these if they exist in your table
          'delta',
          'bundle'
        ])
        ->condition('fp.entity_id', $nid)
        ->orderBy('fp.delta', 'ASC');

      $results = $query->execute()->fetchAllAssoc('delta');

      return $results ?: [];
    }
    catch (\Exception $e) {
      \Drupal::logger('bg_import_webcast')->error('Database query failed: @message', [
        '@message' => $e->getMessage(),
      ]);
      return [];
    }
  }
    protected function loadvideoData(int $nid): array {
        try {
            // Vérifiez d'abord si la table existe
            $table_name = 'field_data_field_video_url';
            if (!$this->getDatabase()->schema()->tableExists($table_name)) {
                // Essayez avec un autre nom de table possible
                $table_name = 'node__field_video_url';
                if (!$this->getDatabase()->schema()->tableExists($table_name)) {
                    return [];
                }
            }

            $query = $this->getDatabase()->select($table_name, 'fp')
                ->fields('fp', [
                    'field_video_url_value',
                ])
                ->condition('fp.entity_id', $nid);

            $results = $query->execute()->fetchAll();

            return $results ?: [];
        }
        catch (\Exception $e) {
            \Drupal::logger('bg_import_webcast')->error('Database query failed: @message', [
                '@message' => $e->getMessage(),
            ]);
            return [];
        }
    }
    protected function findMediaByVideoUrl(string $video_url): ?int {
        $media_storage = \Drupal::entityTypeManager()->getStorage('media');
        $query = $media_storage->getQuery()
            ->condition('field_media_oembed_video', $video_url, '=')->accessCheck();

        $media_ids = $query->execute();

        if (!empty($media_ids)) {
            $first_media_id = (int) reset($media_ids);
            return $first_media_id;
        }

            return NULL;
    }
// Helper function to get related article IDs.
  protected function getRelatedArticleUrls($nid) {
    try {
      // Vérifiez d'abord si la table existe
      $table_name = 'field_data_field_related_articles';
      if (!$this->getDatabase()->schema()->tableExists($table_name)) {
        // Essayez avec un autre nom de table possible
        $table_name = 'node__field_related_articles';
        if (!$this->getDatabase()->schema()->tableExists($table_name)) {
          return [];
        }
      }

        $query = $this->getDatabase()->select($table_name, 'fp')
            ->fields('ua', ['alias'])
            ->fields('n', ['title'])

            ->condition('fp.entity_id', $nid);

        $query->join('url_alias', 'ua', 'ua.source = CONCAT(:source_prefix, fp.field_related_articles_target_id)', [
            ':source_prefix' => 'node/',
        ]);
        $query->join('node', 'n', 'n.nid = fp.field_related_articles_target_id');

// Execute the query

      $results = $query->execute()->fetchAll();
        $urls = [];
        foreach ($results as $result) {
            $urls[] = [
                'url' => 'https://www.buildinggreen.com/' . $result->alias,
                'title' => $result->title,
            ];
        }

        return $urls;
    }
    catch (\Exception $e) {
      \Drupal::logger('bg_import_webcast')->error('Database query failed: @message', [
        '@message' => $e->getMessage(),
      ]);
      return [];
    }

  }
  public function prepareRow(Row $row): bool {

    $nid = (int) $row->getSourceProperty('nid');

      $related_article_urls = $this->getRelatedArticleUrls($nid);
      $field_liens = [];
      foreach ($related_article_urls as $article) {
          $field_liens[] = [
              'uri' => $article['url'],
              'title' => $article['title'],
          ];
      }
      \Drupal::logger('bg_import_webcast')->debug('Presenter values: @values', [
          '@values' => print_r($field_liens, TRUE),
      ]);
      $row->setSourceProperty('field_liens', $field_liens);

    $paragraph_data = $this->loadParagraphData($nid);

    if ($paragraph_data) {
      $presenter_values = [];
      foreach ($paragraph_data as $data) {
        $presenter_values[] = [
          'target_id' => $data->field_presenter_value,
          'target_revision_id' => $data->field_presenter_revision_id,
          'value' => $data->field_presenter_value, // For migration_lookup
          'revision_id' => $data->field_presenter_revision_id, // For migration_lookup
        ];
      }

      // Set the paragraph presenter values into the source property.
      $row->setSourceProperty('field_presenter', $presenter_values);
    }

      $video_data = $this->loadvideoData($nid);
      if (!empty($video_data) && isset($video_data[0]->field_video_url_value)) {
          $video_url = $video_data[0]->field_video_url_value;
          if($video_url){
              $media_id = $this->findMediaByVideoUrl($video_url);
              if ($media_id !== NULL) {
                  $row->setSourceProperty('field_video_target_id', $media_id);
              }
          }
      }

    return parent::prepareRow($row);
  }


}
