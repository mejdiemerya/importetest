<?php
namespace Drupal\bg_product_cart\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Routing\RouteMatchInterface;

class UserRedirectSubscriber implements EventSubscriberInterface {

  protected $currentUser;

  public function __construct(AccountProxyInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 31],
    ];
  }

  public function onKernelRequest(RequestEvent $event) {
    // Ne faire quelque chose que pour des requêtes principales (pas AJAX, pas sous-requêtes)
    if (!$event->isMainRequest()) {
      return;
    }

    // Vérifie que l'utilisateur est connecté
    if (!$this->currentUser->isAuthenticated()) {
      return;
    }

    // Récupère l'objet request
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Vérifie si l'URL commence par /user/
    if (preg_match('#^/user/(\d+)(/.*)?$#', $path)) {
      // Vérifie les rôles
      $roles = $this->currentUser->getRoles();
      if (in_array('lu_og_multiuser', $roles) || in_array('bg_og_multiuser', $roles)) {
        $response = new RedirectResponse('/user/logout?destination=create-profile');
        $event->setResponse($response);
      }
    }
  }
}
