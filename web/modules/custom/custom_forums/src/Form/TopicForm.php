<?php

namespace Drupal\custom_forums\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TopicForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_forums_topic_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['modal'] = array(
      '#type' => 'fieldset',
      '#title' =>  t('Filter by LEED Credit'),
      //'#collapsed' => $has_searched_credits ? TRUE : FALSE,
      '#collapsed' => FALSE,
      '#collapsible' => TRUE,
    );
    $form['modal']['leed_version_modal'] = [
      '#type' => 'select',
      '#empty_option' => t('Choose a LEED version'),
      '#options' =>$this->getLeedVersion(),
      '#prefix' => '<div id="leed_version_modal">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::updateRatingSystemsModal',
        'wrapper' => 'rating_system_modal',
      ],
    ];
    $form['modal']['rating_system_modal'] = [
      '#prefix' => '<div id="rating_system_modal">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#empty_option' => t('Rating system (optional)'),
      '#options' =>  [],
      '#attributes' => ['disabled' => 'disabled'],
      '#ajax' => [
        'callback' => '::updateCreditCategoriesModal',
        'wrapper' => 'credit_category_modal',
      ],
    ];
    $form['modal']['credit_category_modal'] = [
      '#prefix' => '<div id="credit_category_modal">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#empty_option' =>  t('Credit category (optional)'),
      '#options' => [],
      '#attributes' => ['disabled' => 'disabled'],
      '#ajax' => [
        'callback' => '::updateCreditsModal',
        'wrapper' => 'credit_modal',
      ],
    ];

    $form['modal']['credit_modal'] = array(
      '#type' => 'select',
      '#prefix' => '<div id="credit_modal">',
      '#suffix' => '</div>',
      '#empty_option' =>  t('Credit (optional)'),
      '#options' =>[],
      '#attributes' => ['disabled' => 'disabled'],
      '#ajax' => [
        'callback' =>'::ajaxSubmitCallback',
        'event' => 'change',
        'disable-refocus' => FALSE,
        'progress' => 'none',
      ],
    );


    $form['modal']['submit'] = array(
      '#prefix' => '<div id="post-question-submit">',
      '#suffix' => '</div>',
      '#type' => 'submit',
      '#attributes' => ['style' => 'display:none;'],
    );
    $leed_version = $form_state->getValue('leed_version_modal');
    if (!empty($leed_version)) {
      $form['modal']['rating_system_modal']['#options'] = $this->getRatingSystems($leed_version);
      unset($form['modal']['rating_system_modal']['#attributes']['disabled']);

    }
    $rating_system = $form_state->getValue('rating_system_modal');
    if (!empty($rating_system)) {
      $form['modal']['credit_category_modal']['#options'] = $this->getCreditCategories($rating_system);
      unset($form['modal']['credit_category_modal']['#attributes']['disabled']);

    }
    $credit_category = $form_state->getValue('credit_category_modal');
    if (!empty($credit_category)) {
      $form['modal']['credit_modal']['#options'] = $this->getCredits($credit_category);
      unset($form['modal']['credit_modal']['#attributes']['disabled']);

    }


    return $form;
  }
  public function updateCreditCategoriesModal(array &$form, FormStateInterface $form_state) {
    // Get the selected rating system.
    $selected_rating_system = $form_state->getValue('rating_system_modal');

    // Fetch the corresponding credit categories.
    $credit_categories = $this->getCreditCategories($selected_rating_system);

    // Set the options for the credit category select list.
    $form['modal']['credit_category_modal']['#options'] =[''=>t('Credit category (optional)')] + $credit_categories;
    $form_state->setRebuild(TRUE);

    // Return the updated credit category part of the form.
    return $form['modal']['credit_category_modal'];
  }
  public function updateCreditsModal(array &$form, FormStateInterface $form_state) {
    // Get the selected credit category.
    $selected_credit_category = $form_state->getValue('credit_category_modal');

    // Fetch the corresponding credits.
    $credits = $this->getCredits($selected_credit_category);

    // Set the options for the credit select list.
    $form['modal']['credit_modal']['#options'] =[''=>t('Credit (optional)')] + $credits;
    $form_state->setRebuild(TRUE);

    // Return the updated credit part of the form.
    return $form['modal']['credit_modal'];
  }
  public function updateRatingSystemsModal(array &$form, FormStateInterface $form_state) {
    $selected_leed_version = $form_state->getValue('leed_version_modal');

    // Fetch the rating systems based on selected LEED version.
    $rating_systems = $this->getRatingSystems($selected_leed_version);

    $form['modal']['rating_system_modal']['#options'] =['' => t('Rating system (optional)')] +  $rating_systems;
    $form_state->setRebuild(TRUE);

    return $form['modal']['rating_system_modal'];
  }
  function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Get the selected value and update the submit button text.
    $selected_value =  $form_state->getValue('credit_modal');
    if (!empty($selected_value)) {
      $label = $this->getCreditFilterLabel($selected_value);
      $submit_text = sprintf("%s\n%s", $label['title'], $label['description']);
      $form['modal']['submit']['#value'] = $submit_text;
      // Update the submit button text.
      $response->addCommand(new HtmlCommand('#post-question-submit', $form['modal']['submit']));
      // Make the submit button visible.
      // Assuming you have initially set 'display:none;' on the button.
      $response->addCommand(new InvokeCommand('#post-question-submit input[type="submit"]', 'css', ['display', 'inline']));
      // Make the submit button visible.
    }

    return $response;
  }

  function getCreditFilterLabel($tid) {
    $parents = $this->loadAllParentsModal($tid);
    \Drupal::logger('custom_module')->notice('Form state parents: @values', ['@values' => $tid]);
    $count = count($parents);
    $data = array('title' => '', 'description' => '');
    switch ($count) {
      case 1:
        // Only one item means we are looking at LEED Version.
        $data['title'] = strip_tags($parents[0]->name->value);
        break;

      case 2:
        $data['title'] = strip_tags($parents[0]->name->value);
        break;

      case 3:
        $data['title'] = strip_tags(sprintf('%s %s', $parents[1]->name->value, $parents[0]->name->value));
        $data['description'] = strip_tags($parents[0]->description->value);
        break;

      case 4:
      $data['title'] = strip_tags(sprintf('%s %s', $parents[2]->name->value, $parents[0]->name->value));
       $data['description'] = strip_tags($parents[0]->description->value);
        break;
    }
    return $data;
  }
  /**
   * Find all ancestors of a given term ID.
   */
  function loadAllParentsModal($tid) {

    \Drupal::logger('custom_module')->notice('Form state parents tid: @values', ['@values' => $tid]);

    $parents = [];
    $term = Term::load($tid);

    \Drupal::logger('custom_module')->notice('Form state parents tid2: @values', ['@values' =>  json_encode($term)]);

    if (  $term) {
      $parents[] = $term;
      $n = 0;

      while (isset($parents[$n]) && $parent_id = $parents[$n]->parent->target_id) {

        if ($parent = Term::load($parent_id)) {
          $parents[] = $parent;
          $n++;
        } else {
          break;
        }
      }
    }
    \Drupal::logger('custom_module')->notice('Form state parents tid2: @values', ['@values' =>  json_encode($parents)]);


    return $parents;
  }
  protected function getCreditCategories($rating_system_modal_tid) {
    // Load terms corresponding to the selected rating system.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'credit');
    $query->condition('parent', $rating_system_modal_tid); // Fetch child terms of the selected rating system.
    $query->accessCheck(false);

    // Execute the query to get taxonomy term IDs of direct children.
    $tids = $query->execute();

    $credit_categories = [];
    if (!empty($tids)) {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
      foreach ($terms as $term) {
        // Populate the credit categories array with the term ID and name.
        $credit_categories[$term->id()] = $term->getName();
      }
    }

    return $credit_categories;
  }
  protected function getCredits($rating_system_modal_tid) {
    // Load terms corresponding to the selected credit category.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'credit'); // Ensure querying terms from the 'credit' vocabulary.
    $query->condition('parent', $rating_system_modal_tid); // Fetch child terms directly under the selected credit category.
    $query->accessCheck(false);

    // Execute the query to get taxonomy term IDs of direct children.
    $tids = $query->execute();

    $credits = [];
    if (!empty($tids)) {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
      foreach ($terms as $term) {
        // Populate the credits array with the term ID and name.
        $credits[$term->id()] = $term->getName();
      }
    }

    return $credits;
  }
  protected function getRatingSystems($rating_system_modaln_tid) {
    // Load terms corresponding to the selected LEED version.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'credit')->accessCheck(false);

    $query->condition('parent', $rating_system_modaln_tid); // Fetch child terms.
    $tids = $query->execute();
    \Drupal::logger('custom_module')->notice('Form state values2: @values', ['@values' => $tids]);

    $rating_system_modals = [];
    if (!empty($tids)) {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
      foreach ($terms as $term) {
        $rating_system_modals[$term->id()] = $term->getName();
      }
    }

    return $rating_system_modals;
  }
  protected function getLeedVersion() {
    $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('credit', 0, 1); // Level 1 terms.
    $rating_system_modal = [];
    foreach ($tree as $term) {
      $rating_system_modal[$term->tid] ='LEED '.$term->name;
    }
    return $rating_system_modal;
  }
  function getTermIdByName($term_name, $vocabulary) {
    // Load the term storage handler.
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

    // Create an entity query for terms.
    $query = $term_storage->getQuery()
      ->condition('vid', $vocabulary)
      ->condition('name', $term_name)->accessCheck(false);

    // Execute the query and fetch the TID.
    $tids = $query->execute();

    // Return the first TID found, or NULL if none found.
    return !empty($tids) ? reset($tids) : null;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {


    $redirect_url= Url::fromRoute('node.add',['node_type' => 'forum'])->setOption('query',
      ['forum_id' => $this->getTermIdByName('Credit Forums', 'forums'),
        'credit_id'=>$form_state->getValue('credit_modal')])->toString();

    // Perform the redirect.
    $response = new RedirectResponse($redirect_url);
    $response->send();

  }


}
/**
 * AJAX callback to update the submit button based on select input.
 */
