<?php

namespace Drupal\commerce_migrate_commerce\Plugin\migrate\source\commerce1;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Gets Commerce 1 commerce_line_items from source database.
 *
 * @MigrateSource(
 *   id = "commerce1_line_item",
 *   source_module = "commerce_order"
 * )
 */
class LineItem extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('commerce_line_item', 'li')
      ->fields('li');

    if (isset($this->configuration['line_item_type'])) {
      $query->condition('li.type', $this->configuration['line_item_type']);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'line_item_id' => $this->t('Line Item ID'),
      'title' => $this->t('Product title'),
      'order_id' => $this->t('Order ID'),
      'type' => $this->t('Type'),
      'line_item_label' => $this->t('Line Item Label'),
      'quantity' => $this->t('Quantity'),
      'created' => $this->t('Created'),
      'changed' => $this->t('Changes'),
      'data' => $this->t('Data'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $row->setSourceProperty('data', unserialize($row->getSourceProperty('data')));
    $row->setSourceProperty('title', $row->getSourceProperty('line_item_label'));

    // Get the product title from the commerce_product table.
    if ($row->getSourceProperty('type') === 'product') {
      $label = $row->getSourceProperty('line_item_label');
      $query = $this->select('commerce_product', 'cp')
        ->fields('cp', ['title'])
        ->condition('cp.sku', $label);
      $title = $query->execute()->fetchCol();
      $row->setSourceProperty('title', reset($title));
    }

    // Get Field API field values.
    $line_item_id = $row->getSourceProperty('line_item_id');
    $revision_id = $row->getSourceProperty('revision_id');
    foreach (array_keys($this->getFields('commerce_line_item', $row->getSourceProperty('type'))) as $field) {
      $row->setSourceProperty($field, $this->getFieldValues('commerce_line_item', $field, $line_item_id, $revision_id));
    }

    // Include the number of currency fraction digits in all prices.
    $currencyRepository = new CurrencyRepository();
    $prices = ['commerce_unit_price', 'commerce_total'];
    foreach ($prices as $price) {
      $value = $row->getSourceProperty($price);
      if ($value) {
        $currency_code = $value[0]['currency_code'];
        $value[0]['fraction_digits'] = $currencyRepository->get($currency_code)
          ->getFractionDigits();
        $row->setSourceProperty($price, $value);
      }
    }

    $order_id = $row->getSourceProperty('order_id');

    // Get line item counts so the adjustments can be split across lines.
    $query = $this->select('commerce_line_item', 'li')
      ->condition('order_id', $order_id)
      ->condition('type', 'product');
    $query->addExpression('COUNT(line_item_id)', 'num_product_line');
    $query->addExpression('MAX(line_item_id)', 'max_line_item_id');
    $results = $query->execute()->fetchAll();
    $row->setSourceProperty('num_product_line', $results[0]['num_product_line']);
    $row->setSourceProperty('max_line_item_id', $results[0]['max_line_item_id']);

    // Get any shipping line for this order. This is to identify and not
    // migrate shipping price components.
    $query = $this->select('commerce_line_item', 'li')
      ->fields('li')
      ->condition('type', 'shipping')
      ->condition('order_id', $order_id);
    $shipping = $query->execute()->fetchAll();
    $row->setSourceProperty('shipping', $shipping);

    // Get all price components on this order so the discounts can be found
    // and converted to adjustments on the line item.
    $order_id = $row->getSourceProperty('order_id');
    $row->setSourceProperty('order_components', $this->getFieldValues('commerce_order', 'commerce_order_total', $order_id));

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValues($entity_type, $field, $entity_id, $revision_id = NULL, $language = NULL) {
    $values = parent::getFieldValues($entity_type, $field, $entity_id, $revision_id, $language);
    // Unserialize any data blob in these fields.
    foreach ($values as $key => &$value) {
      if (isset($value['data'])) {
        $values[$key]['data'] = unserialize($value['data']);
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['line_item_id']['type'] = 'integer';
    return $ids;
  }

}
