<?php

namespace Drupal\commerce_migrate_commerce\Plugin\migrate\source\commerce1;

use Drupal\commerce_store\Resolver\DefaultStoreResolver;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets Commerce 1 product display data from database.
 *
 * @MigrateSource(
 *   id = "commerce1_product_display",
 *   source_module = "commerce_product"
 * )
 */
class ProductDisplay extends FieldableEntity {

  /**
   * The default store resolver.
   *
   * @var \Drupal\commerce_store\Resolver\DefaultStoreResolver
   */
  protected $defaultStoreResolver;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, DefaultStoreResolver $default_store_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    $this->defaultStoreResolver = $default_store_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('state'),
      $container->get('entity_type.manager'),
      $container->get('commerce_store.default_store_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'nid' => $this->t('Product (variation) ID'),
      'title' => $this->t('Title'),
      'type' => $this->t('Type'),
      'uid' => $this->t('Owner'),
      'status' => $this->t('Status'),
      'created' => $this->t('Created'),
      'changed' => $this->t('Changes'),
      'field_name' => $this->t('Field name for variations'),
      'variations_field' => $this->t('Value of the product reference field'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'n';

    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node', 'n');
    $query->leftJoin('field_config_instance', 'fci', '(n.type = fci.bundle)');
    $query->leftJoin('field_config', 'fc', '(fc.id = fci.field_id)');
    $query->condition('fc.type', 'commerce_product_reference');
    $query->fields('n', [
      'nid',
      'title',
      'type',
      'uid',
      'status',
      'created',
      'changed',
    ]);
    $query->fields('fc', ['field_name']);

    if (isset($this->configuration['product_type'])) {
      $query->condition('n.type', $this->configuration['product_type']);
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $default_store = $this->defaultStoreResolver->resolve();
    if ($default_store) {
      $row->setDestinationProperty('stores', ['target_id' => $default_store->id()]);
    }
    else {
      throw new MigrateException('You must have a store saved in order to import products.');
    }

    $variations_field_name = $row->getSourceProperty('field_name');
    // Get Field API field values.
    $nid = $row->getSourceProperty('nid');
    $vid = $row->getSourceProperty('vid');
    foreach (array_keys($this->getFields('node', $row->getSourceProperty('type'))) as $field) {
      // If this is the product reference field, map it to `variations_field`
      // since it does not have a standardized name.
      if ($field == $variations_field_name) {
        $row->setSourceProperty('variations_field', $this->getFieldValues('node', $variations_field_name, $nid, $vid));
      }
      else {
        $row->setSourceProperty($field, $this->getFieldValues('node', $field, $nid, $vid));
      }
    }
    return parent::prepareRow($row);
  }

}
