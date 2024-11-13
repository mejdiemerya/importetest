<?php

namespace Drupal\custom_forums\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\custom_forums\Form\TopicForm;
use Drupal\taxonomy\Entity\Term;

class TopicController extends ControllerBase {

  /**
   * Display the topic creation form.
   */
  public function add() {
    $form = $this->formBuilder()->getForm(TopicForm::class);

    $render_array = [
      '#theme' => 'item_list',
      '#items' => [],
      '#form' => [],
    ];
// Build a query for terms in the 'forums' vocabulary that have the specified fields.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery()->accessCheck(false);
    $query->condition('vid', 'forums')
      ->notExists('field_credit')
      ->notExists('field_credit_category')
      ->notExists('field_leed_version')
      ->notExists('field_rating_system');

// Execute the query to get term IDs.
    $tids = $query->execute();
    // Load the terms.
    $forums = Term::loadMultiple($tids);

    // Populate the items in the render array with term names or other information.
      foreach ($forums as $forum) {
        $term_name=$forum->get('name')->value;
        // Replac 'your_topic_add_route' with the actual route to add a topic.
        $url = Url::fromRoute('node.add',['node_type' => 'forum'])->setOption('query', ['forum_id' => $forum->id()]);
        // Create a link using the forum's name and the generated URL.
        $link = Link::fromTextAndUrl($term_name, $url)->toRenderable();
        // Add the link to the render array.
        $render_array['#items'][] = $link;
      }
      $render_array['#form'][] = $form;


    return $render_array;
  }
}
