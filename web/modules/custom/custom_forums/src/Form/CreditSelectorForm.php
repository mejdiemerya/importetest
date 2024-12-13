<?php

namespace Drupal\custom_forums\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
class CreditSelectorForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_credit_selector_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/jquery';

    // Extract the taxonomy term ID from the URL
    $route_match = \Drupal::routeMatch();
    $term = $route_match->getParameter('taxonomy_term');



    $credit = $term->id();
    $hierarchy = $this->loadAllParentsModal($credit);

    $leed_version = isset($hierarchy[3]) ? $hierarchy[3]->id() : NULL;
    $rating_system = isset($hierarchy[2]) ? $hierarchy[2]->id() : NULL;
    $credit_category = isset($hierarchy[1]) ? $hierarchy[1]->id() : NULL;

    $form['credit_selector'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['credit-selector']],
      '#prefix' => '<div id="credit-selector-wrapper">',
      '#suffix' => '</div>',
    ];

    // LEED Version Selector
    $form['credit_selector']['leed_version'] = [
      '#type' => 'select',
      '#title' => $this->t('LEED version'),
      '#empty_option' => $this->t('--'),
      '#options' => $this->getLeedVersion(),
      '#default_value' => $leed_version,
      '#prefix' => '<div id="leed_version">',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['disable-chosen']],
      '#ajax' => [
        'callback' => '::updateRatingSystemsModal',
        'wrapper' => 'credit-selector-wrapper',
      ],
    ];

    $leed_version_data = $this->getTermNameAndDescription($leed_version);
    $form['credit_selector']['leed_version_description'] = [
      '#markup' => '<div id="leed-version-label"><span class="description">LEED ' . $leed_version_data['name'] . ': ' . $leed_version_data['description'] . '</span></div>',
    ];

    // Rating System Selector
    $form['credit_selector']['rating_system'] = [
      '#prefix' => '<div id="rating_system">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#title' => $this->t('Rating system'),
      '#empty_option' => $this->t('--'),
      '#options' => !empty($leed_version) ? $this->getRatingSystems($leed_version) : [],
      '#default_value' => $rating_system,
      '#attributes' => ['class' => ['disable-chosen']],
      '#disabled' => empty($leed_version),
      '#ajax' => [
        'callback' => '::updateCreditCategoriesModal',
        'wrapper' => 'credit-selector-wrapper',
      ],
    ];

    $rating_system_data = $this->getTermNameAndDescription($rating_system);
    $form['credit_selector']['rating_system_description'] = [
      '#markup' => '<div id="rating-system-label"><span class="description">' . $rating_system_data['description'] . '</span></div>',
    ];

    // Credit Category Selector
    $form['credit_selector']['credit_category'] = [
      '#prefix' => '<div id="credit_category">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#title' => $this->t('Credit category'),
      '#empty_option' => $this->t('--'),
      '#options' => !empty($rating_system) ? $this->getCreditCategories($rating_system) : [],
      '#default_value' => $credit_category,
      '#attributes' => ['class' => ['disable-chosen']],
      '#disabled' => empty($rating_system),
      '#ajax' => [
        'callback' => '::updateCreditsModal',
        'wrapper' => 'credit-selector-wrapper',
      ],
    ];

    $credit_category_data = $this->getTermNameAndDescription($credit_category);
    $form['credit_selector']['credit_category_description'] = [
      '#markup' => '<div id="credit-category-label"><span class="description">' . $credit_category_data['description'] . '</span></div>',
    ];

    // Credit Selector
    $form['credit_selector']['credit'] = [
      '#prefix' => '<div id="credit">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#title' => $this->t('Credit'),
      '#empty_option' => $this->t('--'),
      '#options' => !empty($credit_category) ? $this->getCredits($credit_category) : [],
      '#default_value' => $credit,
      '#attributes' => ['class' => ['disable-chosen']],
      '#disabled' => empty($credit_category),
      '#ajax' => [
        'callback' =>'::redirectOnSelection',
        'event' => 'change',
        'disable-refocus' => FALSE,
        'progress' => 'none',
      ],
    ];

    $credit_data = $this->getTermNameAndDescription($credit);
    $form['credit_selector']['credit_description'] = [
      '#markup' => '<div id="credit-label"><span class="description">' . $credit_data['description'] . '</span></div>',
    ];

    // Dynamically populate options and remove 'disabled' if values exist in form_state or tempstore
    $leed_version = $form_state->getValue('leed_version') ?? $leed_version;
    if (!empty($leed_version)) {
      $form['credit_selector']['rating_system']['#options'] = $this->getRatingSystems($leed_version);
      unset($form['credit_selector']['rating_system']['#attributes']['disabled']);
    }

    $rating_system = $form_state->getValue('rating_system') ?? $rating_system;
    if (!empty($rating_system)) {
      $form['credit_selector']['credit_category']['#options'] = $this->getCreditCategories($rating_system);
      unset($form['credit_selector']['credit_category']['#attributes']['disabled']);
    }

    $credit_category = $form_state->getValue('credit_category') ?? $credit_category;
    if (!empty($credit_category)) {
      $form['credit_selector']['credit']['#options'] = $this->getCredits($credit_category);
      unset($form['credit_selector']['credit']['#attributes']['disabled']);
    }
    return $form;

}
  /**
   * AJAX callback to update the LEED version description.
   */

  public function updateCreditCategoriesModal(array &$form, FormStateInterface $form_state) {
    // Get the selected rating system.
    $selected_rating_system = $form_state->getValue('rating_system');

    // Fetch the corresponding credit categories.
    $credit_categories = $this->getCreditCategories($selected_rating_system);

    // Set the options for the credit category select list.
    $form['credit_selector']['credit_category']['#options'] =[''=>t('--')] + $credit_categories;
    $form['credit_selector']['credit']['#options'] = ['' => t('--')]; // Reset the options
    $form['credit_selector']['credit']['#attributes']['disabled'] = 'disabled';
    $rating_system_data = $this->getTermNameAndDescription($selected_rating_system);
    $form['credit_selector']['rating_system_description'] = [
      '#markup' => '<div id="rating-system-label"><span class="description">' . $rating_system_data['description'] . '</span></div>',
    ];
    $form['credit_selector']['credit_category_description'] = [
      '#markup' => '<div id="credit-category-label"><span class="description">--</span></div>',
    ];
    $form['credit_selector']['credit_description'] = [
      '#markup' => '<div id="credit-label"><span class="description">--</span></div>',
    ];
    $form_state->setRebuild(TRUE);

    // Return the updated credit category part of the form.
    return $form['credit_selector'] ;
  }
  public function updateCreditsModal(array &$form, FormStateInterface $form_state) {
    $selected_credit_category = $form_state->getValue('credit_category');
      // Get the selected credit category.

      // Fetch the corresponding credits.
      $credits = $this->getCredits($selected_credit_category);
      // Set the options for the credit select list.
      $form['credit_selector']['credit']['#options'] =[''=>t('--')] + $credits;

    $credit_category_data = $this->getTermNameAndDescription($selected_credit_category);
    $form['credit_selector']['credit_category_description'] = [
      '#markup' => '<div id="credit-category-label"><span class="description">' . $credit_category_data['description'] . '</span></div>',
    ];
    $form['credit_selector']['credit_description'] = [
      '#markup' => '<div id="credit-label"><span class="description">--</span></div>',
    ];
    $form_state->setRebuild(TRUE);

    // Return the updated credit part of the form.
    return $form['credit_selector'];
  }
  public function updateRatingSystemsModal(array &$form, FormStateInterface $form_state) {
    $selected_leed_version = $form_state->getValue('leed_version');

    // Fetch the rating systems based on selected LEED version.
    $rating_systems = $this->getRatingSystems($selected_leed_version);

    $form['credit_selector']['rating_system']['#options'] =['' => t('--')] +  $rating_systems;
    $form['credit_selector']['credit_category']['#options'] = ['' => t('--')]; // Reset the options
    $form['credit_selector']['credit_category']['#attributes']['disabled'] = 'disabled';
    $form['credit_selector']['credit']['#options'] = ['' => t('--')]; // Reset the options
    $form['credit_selector']['credit']['#attributes']['disabled'] = 'disabled';
    // Update the LEED version description.
    $leed_version_data = $this->getTermNameAndDescription($selected_leed_version);
    $form['credit_selector']['leed_version_description'] = [
      '#markup' => '<div id="leed-version-label"><span class="description">LEED ' . $leed_version_data['name'] . ': ' . $leed_version_data['description'] . '</span></div>',
    ];
    $form['credit_selector']['rating_system_description'] = [
      '#markup' => '<div id="rating-system-label"><span class="description">Choose one</span></div>',
    ];
    $form['credit_selector']['credit_category_description'] = [
      '#markup' => '<div id="credit-category-label"><span class="description">--</span></div>',
    ];
    $form['credit_selector']['credit_description'] = [
      '#markup' => '<div id="credit-label"><span class="description">--</span></div>',
    ];
    $form_state->setRebuild(TRUE);

    return $form['credit_selector'];
  }

// AJAX callback to update the entire form
  public function redirectOnSelection(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    // Retrieve the selected credit ID from the form state.
    $selected_credit_id = $form_state->getValue('credit');

    $credit_data = $this->getTermNameAndDescription($selected_credit_id);
    $form['credit_selector']['credit_description'] = [
      '#markup' => '<div id="credit-label"><span class="description">' . $credit_data['description'] . '</span></div>',
    ];

    // Ensure the selected credit ID is valid before proceeding.
    if (!empty($selected_credit_id)) {
      // Construct the redirect URL for the taxonomy term.
      $redirect_url = Url::fromRoute('entity.taxonomy_term.canonical', [
        'taxonomy_term' => $selected_credit_id,
      ])->toString();

      // Use the RedirectCommand to redirect via AJAX.
      return $response->addCommand(new RedirectCommand($redirect_url));
    }

    // If no valid ID is selected, do nothing or provide a fallback response.
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
  protected function getCreditCategories($rating_system_tid) {
    // Load terms corresponding to the selected rating system.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'credit');
    $query->condition('parent', $rating_system_tid); // Fetch child terms of the selected rating system.
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
  protected function getCredits($rating_system_tid) {
    // Load terms corresponding to the selected credit category.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'credit'); // Ensure querying terms from the 'credit' vocabulary.
    $query->condition('parent', $rating_system_tid); // Fetch child terms directly under the selected credit category.
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
  protected function getRatingSystems($rating_systemn_tid) {
    // Load terms corresponding to the selected LEED version.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'credit')->accessCheck(false);

    $query->condition('parent', $rating_systemn_tid); // Fetch child terms.
    $tids = $query->execute();
    \Drupal::logger('custom_module')->notice('Form state values2: @values', ['@values' => $tids]);

    $rating_systems = [];
    if (!empty($tids)) {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
      foreach ($terms as $term) {
        $rating_systems[$term->id()] = $term->getName();
      }
    }

    return $rating_systems;
  }
  protected function getLeedVersion() {
    $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('credit', 0, 1); // Level 1 terms.
    $rating_system = [];
    foreach ($tree as $term) {
      $rating_system[$term->tid] ='LEED '.$term->name;
    }
    return $rating_system;
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
  protected function getTermNameAndDescription($tid) {
    // Load the taxonomy term entity by its ID.
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);

    // Initialize an empty array to store term data.
    $term_data = [];

    // Check if the term is loaded successfully.
    if ($term) {
      // Retrieve the term name.
      $term_data['name'] = $term->getName();

      // Retrieve the description, ensuring the field exists and is not empty.
      $description_field = $term->get('description');
      if (!$description_field->isEmpty()) {
        $term_data['description'] = $description_field->value;
      } else {
        $term_data['description'] = '';
      }
    }

    // Return the term data with name and description.
    return $term_data;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response=$this->redirectOnSelection($form,  $form_state);
    $response->send();

  }


}

