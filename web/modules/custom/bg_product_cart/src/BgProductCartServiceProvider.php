<?php

declare(strict_types=1);

namespace Drupal\bg_product_cart;

use Drupal\bg_product_cart\alter\PriceListRepository;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Defines a service provider for the bg_product_cart module.
 *
 * @see https://www.drupal.org/node/2026959
 */
final class BgProductCartServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    // @DCG Example of how to swap out existing service.
    // @code
       if ($container->hasDefinition('commerce_pricelist.repository')) {
         $container->getDefinition('commerce_pricelist.repository')
           ->setClass(PriceListRepository::class);
       }
    // @endcode
  }

}
