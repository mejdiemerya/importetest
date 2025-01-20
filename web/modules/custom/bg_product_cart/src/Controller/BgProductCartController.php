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


  public function addDiscount($pid) {
    $account =   $this->currentUser ;
    $cart = FALSE;
    $store = $this->entityTypeManager->getStorage('commerce_store')  ->loadDefault();
    /**
     * @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage
     */
    $order_item_storage = $this->entityTypeManager->getStorage('commerce_order_item');
    $product = $this->entityTypeManager->getStorage('commerce_product')->load((int) $pid);

    // remove cart
    $carts = $this->cartProvider->getCarts($account);
    foreach ($carts as $ct) {
      $this->cartManager->emptyCart($ct);
    }

    if(!empty($product->get('field_related_products'))) {

      foreach ($product->get('field_related_products') as $discount_product) {

        $variation = $this->entityTypeManager->getStorage('commerce_product_variation')->load((int)$discount_product->getValue()["target_id"]);


        if(!empty($variation)){
          /**
           * @var \Drupal\commerce_order\Entity\OrderItem $order_item
           */
          $order_item = $order_item_storage->createFromPurchasableEntity($variation);

          $order_type_id = $this->orderTypeResolver->resolve($order_item);
          $order_item->setTitle($variation->getOrderItemTitle());
          $cart = $this->cartProvider->getCart($order_type_id, $store, $account);

          if (empty($cart)) {
            $cart = $this->cartProvider->createCart($order_type_id, $store, $account);
          }

          $order_item->set('order_id', $cart->id());

          //dd($order_item);
          $this->cartManager->addOrderItem($cart, $order_item, false);

        }
      //  $variables['discount_products'][] = $discount_product->getValue()["target_id"];
      }

      $redirectURL = 'checkout/'.$cart->id().'/order_information';
      return new RedirectResponse(base_path().$redirectURL);
    }
  // dd($product);
      $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }
  private function addToCart($vid)
  {
    $account =   $this->currentUser ;
    $cart = FALSE;
    $store = $this->entityTypeManager->getStorage('commerce_store')  ->loadDefault();
    /**
     * @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage
     */
    $order_item_storage = $this->entityTypeManager->getStorage('commerce_order_item');
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

  }

  private function checkNoPurchase($variation, $product){
    $account =   $this->currentUser ;
    //bg PN member
    $is_pn_member = FALSE;
    if (in_array('bg_communities', $account->getRoles())) {
      $is_pn_member = TRUE;
    }

    //BG and LU premium users get same access
    $is_bg_or_lu_premium = FALSE;
    if ( $account->hasPermission('access premium content') || $account->hasPermission('access LEEDuser premium content')) {
      $is_bg_or_lu_premium = TRUE;
    }

    $variables['hide_buy_button'] = FALSE;
    if (!empty($product->get('field_hide_buy_button')) && $product->get('field_hide_buy_button')->value == 1)
    {
      return TRUE;
    }

    $resultsOders = getOrderByUserAndProduct($account->id(), $variation->id());

    if(!empty($resultsOders)
      || (!empty($product->get('field_free_to_pn_members')) && $product->get('field_free_to_pn_members')->value == 1 && $is_pn_member)
      || (!empty($product->get('field_free_to_premium_users')) && $product->get('field_free_to_premium_users')->value == 1 && $is_bg_or_lu_premium)
    ) {

      return TRUE;
    }
    return FALSE;
  }
  public function addVariation($pid, $vid) {


    $account =   $this->currentUser ;
    $cart = FALSE;
    $store = $this->entityTypeManager->getStorage('commerce_store')  ->loadDefault();
    /**
     * @var \Drupal\commerce_order\OrderItemStorageInterface $order_item_storage
     */
    $order_item_storage = $this->entityTypeManager->getStorage('commerce_order_item');


    $product = \Drupal\commerce_product\Entity\Product::load((int)$pid);

    /*Load Product Variations*/

    $variation = $this->entityTypeManager->getStorage('commerce_product_variation')->load((int)$vid);
    if(!empty($variation)){
      // $this->checkNoPurchase($variation, $product);
    //  dd("");
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


  }
}
