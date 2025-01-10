<?php

declare(strict_types=1);

namespace Drupal\bg_product_cart\Controller;

use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for bg_product_cart routes.
 */
final class BgProductCartController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The order type resolver.
   *
   * @var \Drupal\commerce_order\Resolver\OrderTypeResolverInterface
   */
  protected $orderTypeResolver;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;


  protected $currentUser;

  public function __construct(AccountInterface $currentUser, EntityTypeManagerInterface $entity_type_manager ) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entity_type_manager;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->cartManager = $container->get('commerce_cart.cart_manager');
    $instance->orderTypeResolver = $container->get('commerce_order.chain_order_type_resolver');
    $instance->cartProvider = $container->get('commerce_cart.cart_provider');
    return $instance;
  }

  public function addVariation($pid, $vid) {

    $account =   $this->currentUser ;
    $cart = FALSE;
    $store = $this->entityTypeManager->getStorage('commerce_store') ->loadDefault();
    /**
     * @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage
     */
    $order_item_storage = $this->entityTypeManager->getStorage('commerce_order_item');


    $product = \Drupal\commerce_product\Entity\Product::load((int)$pid);

    /*Load Product Variations*/

    $variation = $this->entityTypeManager->getStorage('commerce_product_variation')->load((int)$vid);
    if(!empty($variation)){
      /**
       * @var \Drupal\commerce_order\Entity\OrderItem $order_item
       */
      $order_item = $order_item_storage->createFromPurchasableEntity($variation);
      $order_type_id = $this->orderTypeResolver->resolve($order_item);
      $order_item->setTitle($variation->getOrderItemTitle());
      $cart = $this->cartProvider->getCart($order_type_id, $store, $account);
      if (!empty($cart)) {
        $this->cartManager->emptyCart($cart);
      }
      if (!$cart) {
        $cart = $this->cartProvider->createCart($order_type_id, $store, $account);
      }

      $order_item->set('order_id', $cart->id());
      $this->cartManager->addOrderItem($cart, $order_item, true);

    }

   // \Drupal::messenger()->deleteAll();
    $redirectURL = 'checkout/'.$cart->id().'/order_information';
    return new RedirectResponse(base_path().$redirectURL);


//    $build['content'] = [
//      '#type' => 'item',
//      '#markup' => $this->t('It works!'),
//    ];
//
//    return $build;

  }
}
