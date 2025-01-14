<?php

namespace Drupal\bg_product_cart\alter;
use Drupal\commerce\Context;
use Drupal\commerce_pricelist\PriceListRepository as BasePriceListRepository;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

class PriceListRepository extends BasePriceListRepository
{

  /**
   * Loads the available price list IDs for the given bundle and context.
   *
   * @param string $bundle
   *   The price list bundle.
   * @param \Drupal\commerce\Context $context
   *   The context.
   *
   * @return int[]
   *   The price list IDs.
   */
  protected function loadPriceListIds($bundle, Context $context) {
    $customer_id = $context->getCustomer()->id();
    $store_id = $context->getStore()->id();
    $date = DrupalDateTime::createFromTimestamp($context->getTime(), $context->getStore()->getTimezone());
    $now = $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
    $cache_key = implode(':', [$bundle, $customer_id, $store_id, $now]);
    if (array_key_exists($cache_key, $this->priceListIds)) {
      return $this->priceListIds[$cache_key];
    }

    $price_list_storage = $this->entityTypeManager->getStorage('commerce_pricelist');
    $query = $price_list_storage->getQuery();
    $query
      ->condition('type', $bundle)
      ->condition('stores', [$store_id], 'IN')
      ->condition($query->orConditionGroup()
        ->condition('customers', $customer_id)
        //->notExists('customers')
        ->condition('customer_roles', $context->getCustomer()->getRoles(), 'IN')
       // ->notExists('customer_roles')
      )
      ->condition('start_date', $now, '<=')
      ->condition($query->orConditionGroup()
        ->condition('end_date', $now, '>')
        ->notExists('end_date')
      )
      ->condition('status', TRUE)
      ->sort('weight')
      ->sort('id', 'DESC')
      ->addTag('commerce_pricelist_query')
      ->addMetaData('customer_id', $customer_id)
      ->addMetaData('store_id', $store_id)
      ->addMetaData('context', $context);
      //->addTag('debug');
     //  echo ( $query->accessCheck(FALSE)->__toString());die;
    $result = $query->accessCheck(FALSE)->execute();
    $price_list_ids = array_values($result);
    $this->priceListIds[$cache_key] = $price_list_ids;

    return $price_list_ids;
  }
}
