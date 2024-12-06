<?php

namespace Drupal\custom_forums\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\custom_forums\Form\TopicForm;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TopicController extends ControllerBase {
  protected $currentUser;

  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  public function add() {
    $output = [];
    $form = \Drupal::formBuilder()->getForm(TopicForm::class);

      $output = [
        '#theme' => 'post_question_modal',
        '#form' => $form,
        '#is_anonymous' => $this->currentUser->isAnonymous(),
        '#is_authenticated' => $this->currentUser->isAuthenticated(),
      ];


    return $output;
  }
}
