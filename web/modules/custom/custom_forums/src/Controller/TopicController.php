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
    if ($this->currentUser->isAnonymous()) {
      $output['#theme'] = 'anonymous_message';
    }
    else {
      $form = \Drupal::formBuilder()->getForm(TopicForm::class);
      $ForumsList = [];

      $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery()->accessCheck(false);
      $query->condition('vid', 'forums')
        ->notExists('field_credit')
        ->notExists('field_credit_category')
        ->notExists('field_leed_version')
        ->notExists('field_rating_system')
        ->sort('tid', 'ASC');

      $tids = $query->execute();
      $forums = Term::loadMultiple($tids);

      foreach ($forums as $forum) {
        $term_name = $forum->get('name')->value;
        $url = Url::fromRoute('node.add', ['node_type' => 'forum'])
          ->setOption('query', ['forum_id' => $forum->id()]);
        $link = Link::fromTextAndUrl($term_name, $url)->toRenderable();
        $ForumsList[] = $link;
      }

      $output = [
        '#theme' => 'authenticated_forum_list',
        '#forums' => $ForumsList,
        '#form' => $form,
      ];
    }

    return $output;
  }
}
