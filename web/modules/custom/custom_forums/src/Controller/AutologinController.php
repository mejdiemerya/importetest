<?php

namespace Drupal\custom_forums\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\og\Og;
use Drupal\user\Entity\User;
use Drupal\user\UserAuthInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AutologinController extends ControllerBase {

  protected $userAuth;
  protected $sessionManager;
  protected $accountSwitcher;
  protected $database;

  protected $currentPath;

  public function __construct(
    UserAuthInterface $userAuth,
    SessionManagerInterface $sessionManager,
    CurrentPathStack $current_path
  ) {
    $this->userAuth = $userAuth;
    $this->sessionManager = $sessionManager;
    $this->currentPath = $current_path;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.auth'),
      $container->get('session_manager'),
      $container->get('path.current')
    );
  }

  public function login($username, $password) {
    $uid = $this->userAuth->authenticate($username, $password);
    if ($uid) {
      if (!$this->ip_login_check_path()) {
        return NULL;
      }
      $account = User::load($uid);
      if ($account && $account->isActive()) {
        $memberships = Og::getMemberships($account);

        foreach ($memberships as $membership) {
          $group = $membership->getGroup();
          $group_id = $group->id();
          $group_type = $group->getEntityTypeId();

          $query = \Drupal::database()->select('og_membership', 'ogm');
          $query->join('users_field_data', 'u', 'ogm.uid = u.uid');
          $query->join('user__roles', 'ur', 'u.uid = ur.entity_id');
          $query->fields('u', ['uid']);
          $query->condition('ogm.entity_id', $group_id);
          $query->condition('ogm.entity_type', $group_type);
          $query->condition('ur.roles_target_id', 'bg_og_multiuser');
          $query->condition('u.status', 1);
          $multiuser_uid = $query->execute()->fetchField();
          if ($multiuser_uid) {
            $user = User::load($multiuser_uid);
            break;
          }
        }
        if (!$user || !$user->isActive()) {
          return NULL;
        }
        // Regenerate session & switch user.
        $this->sessionManager->regenerate();
        user_login_finalize($user);

        return new RedirectResponse('/');
      }
    }

    throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
  }

  public function ip_login_check_path(): bool {
    $path = $this->currentPath->getPath();
    $deny_paths = [
      'user/*',
      'create-profile',
      'signup',
    ];

    foreach ($deny_paths as $pattern) {
      $regex = '@^' . str_replace('\*', '.*', preg_quote($pattern, '@')) . '$@i';
      if (preg_match($regex, trim($path, '/'))) {
        return FALSE;
      }
    }
    return TRUE;
  }
}
