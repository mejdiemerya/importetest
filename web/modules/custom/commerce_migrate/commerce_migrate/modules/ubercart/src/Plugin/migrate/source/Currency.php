<?php

namespace Drupal\commerce_migrate_ubercart\Plugin\migrate\source;

use Drupal\commerce_price\CurrencyImporter;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\Variable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets the Ubercart currency data.
 *
 * @MigrateSource(
 *   id = "uc_currency",
 *   source_module = "uc_store"
 * )
 */
class Currency extends Variable {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The variable names to fetch.
   *
   * @var array
   */
  protected $variables;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    $this->languageManager = $language_manager;
    $this->variables = $this->configuration['variables'];
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
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return parent::fields() +
      [
        'currency_name' => $this->t('Currency name'),
        'numeric_code' => $this->t('Currency code numeric code'),
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get the currency name and the country numeric code by using the
    // destination's currency importer. These values are not available from
    // the ubercart source.
    // @todo find a better to get the currency name and the country numeric code
    // without peaking into the destination.
    $currency_code = $row->getSourceProperty('uc_currency_code');
    $currencyImporter = new CurrencyImporter($this->entityTypeManager, $this->languageManager);
    $currency = $currencyImporter->import($currency_code);
    $name = $currency->getName();
    $numeric_code = $currency->getNumericCode();
    $row->setSourceProperty('currency_name', $name);
    $row->setSourceProperty('numeric_code', $numeric_code);
    return parent::prepareRow($row);
  }

}
