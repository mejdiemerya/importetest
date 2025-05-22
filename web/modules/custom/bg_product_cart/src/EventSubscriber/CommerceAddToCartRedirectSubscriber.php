<?php
namespace Drupal\bg_product_cart\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CommerceAddToCartRedirectSubscriber implements EventSubscriberInterface {

  protected $currentUser;
  protected $requestStack;

  public function __construct(AccountProxyInterface $currentUser, RequestStack $request_stack) {
    $this->currentUser = $currentUser;
    $this->requestStack = $request_stack;
  }

  public static function getSubscribedEvents() {
    return [
      CartEvents::CART_ENTITY_ADD => 'onAddToCart',
    ];
  }

  public function onAddToCart(CartEntityAddEvent $event) {
    $user = $this->currentUser;

    if ($user->isAuthenticated()) {
      $roles = $user->getRoles();

      if (in_array('lu_og_multiuser', $roles) || in_array('bg_og_multiuser', $roles)) {

//        $response = new RedirectResponse('/user/logout?destination=create-profile');
//        $response->send();
//        exit; // Important pour stopper le flux après redirection
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request->headers->get('referer') ?? '/';
        $separator = strpos($referer, '?') !== false ? '&' : '?';
        $url = $referer . $separator . 'show_modal=1';

        $response = new TrustedRedirectResponse($url);
        $response->send();
        exit;

      }
    }
  }
}
