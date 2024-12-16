<?php

namespace Drupal\custom_forums\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Class BgLeeduserSearchForm.
 */
class LeeduserSearchForm extends FormBase {
  /**
   * The private temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a BgLeeduserSearchForm.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, MessengerInterface $messenger) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bg_leeduser_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $store = $this->tempStoreFactory->get('leeduser_search'); // Adjust namespace as needed.
    // Attempt to retrieve saved values if they exist.
    $saved_values = $store->get('saved_values') ?: [];
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/jquery';
    $form['#attached']['library'][]='core/drupal.dialog';

    // Get the current request
    $request = \Drupal::request();

// Retrieve query parameters from the current URL
    $query_params = $request->query->all();
// Initialize variables to store extracted IDs
    $forum_id = null;
    $credit_id = null;
    $location_id = null;
    $tipsheet_id= null;
// Check if the 'f' parameter contains multiple items.
    if (isset($query_params['f'])) {
      // Loop through each parameter to extract key-value pairs.
      foreach ($query_params['f'] as $param) {
        if (preg_match('/^forum:(\d+)$/', $param, $matches)) {
          $forum_id = $matches[1];
        } elseif (preg_match('/^credit:(\d+)$/', $param, $matches)) {
          $credit_id = $matches[1];
        } elseif (preg_match('/^location:(\d+)$/', $param, $matches)) {
          $location_id = $matches[1];
        }
        elseif (preg_match('/^tipsheet:(\d+)$/', $param, $matches)) {
          $tipsheet_id = $matches[1];
        }
      }
    }
    $active_search_parents=[];
    if($credit_id!== null) {

      $active_search_parents = $this->loadAllParents($credit_id);
    }
      if (count($active_search_parents) == 4 ){

        $post_question_url = Url::fromRoute('node.add', [
          'node_type' => 'forum'
        ], [
          'query' => [
            'credit_id' => (int) $credit_id,
            'forum_id' => $this->getTermIdByName('Credit Forums', 'forums'),
            'destination' => 'forums',
          ]
        ])->toString();
        $form['post_question'] = array(
          '#prefix' => '<a id="post-question" href="' . $post_question_url . '"><i class="fi flaticon-communication"></i>',
          '#suffix' => '</a>',
          '#markup' => t('Post a question or comment'),
        );


    }
    elseif ($forum_id !== null) {
      $post_question_url = Url::fromRoute('node.add',
        [
        'node_type' => 'forum'
      ], [
        'query' => [
          'forum_id' => $forum_id,
        ],
      ])->toString();
      $form['post_question'] = [
        '#prefix' => '<a id="post-question" href="' . $post_question_url . '"><i class="fi flaticon-communication"></i>',
        '#suffix' => '</a>',
        '#markup' => t('Post a question or comment'),
      ];

    }
      elseif ( $tipsheet_id !== null) {
        $post_question_url = Url::fromRoute('node.add',
          [
            'node_type' => 'forum'
          ], [
            'query' => [
              'tipsheet_id' => $tipsheet_id,
              'forum_id' => $this->getTermIdByName('Tipsheet Forum', 'forums'),
            ],
          ])->toString();
        $form['post_question'] = [
          '#prefix' => '<a id="post-question" href="' . $post_question_url . '"><i class="fi flaticon-communication"></i>',
          '#suffix' => '</a>',
          '#markup' => t('Post a question or comment'),
        ];
      }
      else {

      // Post Question Link.
      $form['post_question'] = [
        '#type' => 'markup',
        '#prefix' => '<a id="post-question" href="#" data-bs-toggle="modal" data-bs-target="#post-question-modal"  ><i class="fi flaticon-communication"></i>',
        '#suffix' => '</a>',
        '#markup' => t('Post a question or comment'),
      ];
    }
    $form['clear_search'] = array(
      '#type' => 'submit',
      '#name' => 'clear_search',
      '#description' => t('Clear search'),
      '#title' => t('Clear search '),
      '#title_display' => 'invisible',
      '#submit' => ['::clearSearch'],
      '#prefix' => '<div id="clear-search">',
      '#suffix' => '</div>',
      '#value' => "\xC3\x97",
    );
    $form['keyword_search'] = [
      '#type' => 'textfield',
      '#title' => t('Search Forums'),
      '#default_value' => isset($saved_values['keyword_search']) ? $saved_values['keyword_search'] : '',
      '#attributes' => [
        'placeholder' => t('Search within forums'),
      ],
      '#prefix' => '<div id="keyword-search">',
      '#suffix' => '</div>',
    ];
    // Display active location filters if they exist.
    if (!empty($saved_values['location'])) {
      $form['active_location_filter_fieldset'] = [
        '#type' => 'fieldset',
        '#prefix' => '<div id="active_location_filter_fieldset">',
        '#suffix' => '</div>',
      ];
      $form['active_location_filter_fieldset']['clear_location'] = array(
        '#type' => 'submit',
        '#name' => 'clear_location',
        '#description' => t('Immediately clear all location filters'),
        '#title' => t('Immediately clear all location filters'),
        '#submit' => ['::clearLocation'], // Use a clear handler.
        '#value' => "\xC3\x97",
      );
      $form['active_location_filter_fieldset']['location_queries'] = array(
        '#type' => 'fieldset',
        '#prefix' => '<div id="active_location_filter_fieldset_location_queries">',
        '#suffix' => '</div>',
        '#title' => t('Location Filters'),
      );
      // Assuming $filter contains term IDs, considering normalization if necessary.
      $term_id = $saved_values['location'];

      // Load all terms based on the IDs to create user-friendly descriptions.
      $term = Term::load($term_id);
        $form['active_location_filter_fieldset']['location_queries']['saved_query_location_' . $term->id()] = [
          '#type' => 'checkbox',
          '#title' => $term->getName(),
          '#description' => $term->getDescription(),
          '#default_value' => 1,
          '#prefix' => sprintf('<div id="saved_query_location_%d">', $term->id()),
          '#suffix' => '</div>',
        ];
    }
    if (!empty(($saved_values['credits']))) {
      $form['active_credit_filter_fieldset']['clear_credits'] = array(
        '#type' => 'submit',
        '#name' => 'clear_credits',
        '#description' => t('Immediately clear all credit filters'),
        '#title' => t('Immediately clear all credit filters'),
        '#title_display' => 'invisible',
        '#submit' => ['::clearCredits'],
        '#value' => "\xC3\x97",
      );
      $form['active_credit_filter_fieldset']['queries'] = array(
        '#type' => 'fieldset',
        '#title' => t('Credit Filters'),
      );
     $credit_id = $saved_values['credits'];
        if ($term = Term::load($credit_id)) {
          $label = getCreditFilterLabel($credit_id);
          $form['active_credit_filter_fieldset']['queries']['saved_query_credit_' . $credit_id] = array(
            '#type' => 'checkbox',
            '#title' => $label['title'],
            '#suffix' => '<div class="credit-query-description">' . htmlspecialchars($label['description'], ENT_QUOTES, 'UTF-8') . '</div>',
            '#default_value' => 1,
          );
        }

    }

    $form['credit_filter_fieldset'] = array(
      '#type' => 'fieldset',
      '#title' =>  t('Filter by LEED Credit'),
      //'#collapsed' => $has_searched_credits ? TRUE : FALSE,
      '#collapsed' => FALSE,
      '#collapsible' => TRUE,
    );
    $form['credit_filter_fieldset']['leed_version'] = [
      '#type' => 'select',
      '#empty_option' => t('Choose a LEED version'),
      '#options' =>$this->getLeedVersion(),
      '#prefix' => '<div id="leed_version">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::updateRatingSystems',
        'wrapper' => 'rating_system',
      ],
    ];
    $form['credit_filter_fieldset']['rating_system'] = [
      '#prefix' => '<div id="rating_system">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#empty_option' => t('Rating system (optional)'),
      '#options' =>  [],
      '#attributes' => ['disabled' => 'disabled'],
      '#ajax' => [
        'callback' => '::updateCreditCategories',
        'wrapper' => 'credit_category',
      ],
    ];
    $form['credit_filter_fieldset']['credit_category'] = [
      '#prefix' => '<div id="credit_category">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#empty_option' =>  t('Credit category (optional)'),
      '#options' => [],
      '#attributes' => ['disabled' => 'disabled'],
      '#ajax' => [
        'callback' => '::updateCredits',
        'wrapper' => 'credit',
      ],
    ];

    $form['credit_filter_fieldset']['credit'] = array(
      '#type' => 'select',
      '#prefix' => '<div id="credit">',
      '#suffix' => '</div>',
      '#empty_option' =>  t('Credit (optional)'),
      '#options' =>[],
      '#attributes' => ['disabled' => 'disabled'],
    );

    $form['location_filter_fieldset'] = array(
      '#type' => 'fieldset',
      '#title' => t('Filter by location'),
      '#collapsed' => FALSE,
      '#collapsible' => TRUE,
      '#prefix' => '<div id="location_fieldset">',
      '#suffix' => '</div>',
    );
    $form['location_filter_fieldset']['location_region'] = array(
      '#type' => 'select',
      '#empty_option' => t('Choose a region'),
      '#options' => $this->getLocationRegions(),
      '#ajax' => [
        'callback' => '::updateLocationCountry',
        'wrapper' => 'location_country', // ID of the element to be updated.
      ],
    );
    // Only display country if we have a region in the form state or in the URL
    // from a previous query.
    $form['location_filter_fieldset']['location_country'] = array(
      '#type' => 'select',
      '#empty_option' => t('Choose a country (optional)'),
      '#prefix' => '<div id="location_country">',
      '#suffix' => '</div>',
      '#options' => [],
      '#attributes' => ['disabled' => 'disabled'],
    );
    $form['show_results'] = [
      '#type' => 'submit',
      '#attributes' =>[
        'class' => ['submit-on-enter'],
      ],
      '#value' => t('Show Results'),
      '#prefix' => '<div id="show-results">',
      '#suffix' => '</div>',

    ];
    // Check for default values in form state and set them.
    $selected_region = $form_state->getValue('location_region');
    if (!empty($selected_region)) {
      $form['location_filter_fieldset']['location_country']['#options'] = $this->getCountriesByRegion($selected_region);
      unset($form['location_filter_fieldset']['location_country']['#attributes']['disabled']);

    }
    $leed_version = $form_state->getValue('leed_version');
    if (!empty($leed_version)) {
      $form['credit_filter_fieldset']['rating_system']['#options'] = $this->getRatingSystems($leed_version);
      unset($form['credit_filter_fieldset']['rating_system']['#attributes']['disabled']);

    }
    $rating_system = $form_state->getValue('rating_system');
    if (!empty($rating_system)) {
      $form['credit_filter_fieldset']['credit_category']['#options'] = $this->getCreditCategories($rating_system);
      unset($form['credit_filter_fieldset']['credit_category']['#attributes']['disabled']);

    }
    $credit_category = $form_state->getValue('credit_category');
    if (!empty($credit_category)) {
      $form['credit_filter_fieldset']['credit']['#options'] = $this->getCredits($credit_category);
      unset($form['credit_filter_fieldset']['credit']['#attributes']['disabled']);

    }
    return $form;
  }
  public function updateCreditCategories(array &$form, FormStateInterface $form_state) {
    // Get the selected rating system.
    $selected_rating_system = $form_state->getValue('rating_system');

    // Fetch the corresponding credit categories.
    $credit_categories = $this->getCreditCategories($selected_rating_system);

    // Set the options for the credit category select list.
    $form['credit_filter_fieldset']['credit_category']['#options'] =[''=>t('Credit category (optional)')] + $credit_categories;
    $form_state->setRebuild(TRUE);

    // Return the updated credit category part of the form.
    return $form['credit_filter_fieldset']['credit_category'];
  }
  public function updateCredits(array &$form, FormStateInterface $form_state) {
    // Get the selected credit category.
    $selected_credit_category = $form_state->getValue('credit_category');

    // Fetch the corresponding credits.
    $credits = $this->getCredits($selected_credit_category);

    // Set the options for the credit select list.
    $form['credit_filter_fieldset']['credit']['#options'] =[''=>t('Credit (optional)')] + $credits;
    $form_state->setRebuild(TRUE);

    // Return the updated credit part of the form.
    return $form['credit_filter_fieldset']['credit'];
  }
  public function updateRatingSystems(array &$form, FormStateInterface $form_state) {
    $selected_leed_version = $form_state->getValue('leed_version');

    // Fetch the rating systems based on selected LEED version.
    $rating_systems = $this->getRatingSystems($selected_leed_version);

    $form['credit_filter_fieldset']['rating_system']['#options'] =['' => t('Rating system (optional)')] +  $rating_systems;
    $form_state->setRebuild(TRUE);

    return $form['credit_filter_fieldset']['rating_system'];
  }
  /**
   * AJAX callback to update the country field based on selected region.
   */
  public function updateLocationCountry(array &$form, FormStateInterface $form_state) {
    // Get the selected region term ID.
    $region_tid = $form_state->getValue('location_region');
    // Fetch child terms (countries) for the selected region.
    $countries = $this->getCountriesByRegion($region_tid);
    // Update the options for the location_country field.

    $form['location_filter_fieldset']['location_country']['#options'] = ['' => $this->t('Choose a country (optional)')] + $countries;
    $form_state->setRebuild(TRUE);

    return $form['location_filter_fieldset']['location_country'];
  }
  /**
   * Submit handler to clear credit filters.
   */
  public function clearCredits(array &$form, FormStateInterface $form_state) {
    // Access the TempStore instance for the current user.
    $store = $this->tempStoreFactory->get('leeduser_search');

    // Retrieve and modify the current values in TempStore.
    $current_values = $store->get('saved_values') ?: [];

    // Check if there are credit-related values to clear and remove them.
    if (isset($current_values['credits'])) {
      unset($current_values['credits']);

      // Update TempStore with the modified values.
      $store->set('saved_values', $current_values);
    }

    // Optional: Clear other related data or session state if needed.

    // Retrieve the current URI and query parameters.
    $current_uri = \Drupal::service('path.current')->getPath();
    $current_query = \Drupal::request()->query->all();

    // Check and remove credit-related parts in the 'f' query parameter or other queries.
    if (isset($current_query['f']) && is_array($current_query['f'])) {
      foreach ($current_query['f'] as $key => $value) {
        if (strpos($value, 'credit:') === 0) {
          unset($current_query['f'][$key]);
        }
      }

      // Clean up the 'f' array, if empty.
      if (empty($current_query['f'])) {
        unset($current_query['f']);
      }
    }

    // Create a new URL without the cleared 'credit' parameters and redirect.
    $url = \Drupal\Core\Url::fromUserInput($current_uri, ['query' => $current_query]);
    $response = new RedirectResponse($url->toString());
    $response->send();

    // Ensure no further form processing happens after redirection.
    $form_state->setRebuild(FALSE);
  }
  /**
   * Submit handler to clear location filters.
   */
  public function clearLocation(array &$form, FormStateInterface $form_state) {
    $store = $this->tempStoreFactory->get('leeduser_search');

    // Clear location-related entries in TempStore.
    $current_values = $store->get('saved_values') ?: [];
    $location_values_cleared = false;

    if (isset($current_values['country'])) {
      unset($current_values['country']);
      $location_values_cleared = true;
    }

    if (isset($current_values['region'])) {
      unset($current_values['region']);
      $location_values_cleared = true;
    }

    if ($location_values_cleared) {
      $store->set('saved_values', $current_values);
    }

    // Retrieve the current URI and query parameters to clean the location filter.
    $current_uri = \Drupal::service('path.current')->getPath();
    $current_query = \Drupal::request()->query->all();

    // Check and remove location-related parts in the 'f' query parameter or other location-specific queries.
    if (isset($current_query['f']) && is_array($current_query['f'])) {
      foreach ($current_query['f'] as $key => $value) {
        if (strpos($value, 'location:') === 0) {
          unset($current_query['f'][$key]);
        }
      }

      // Clean up if the 'f' array is empty after removing.
      if (empty($current_query['f'])) {
        unset($current_query['f']);
      }
    }

    // Recreate the URL without the cleared 'location' parameters.
    $url = \Drupal\Core\Url::fromUserInput($current_uri, ['query' => $current_query]);

    // Redirect to the cleaned URL.
    $response = new RedirectResponse($url->toString());
    $response->send();

    // Prevent further form processing since we redirect.
    $form_state->setRebuild(FALSE);
  }

  /**
   * Handler to clear the keyword search field and reset the URL.
   */
  public function clearSearch(array &$form, FormStateInterface $form_state) {
    $store = $this->tempStoreFactory->get('leeduser_search');

    // Clear the specific entry for keyword_search in TempStore.
    $current_values = $store->get('saved_values') ?: [];
    if (isset($current_values['keyword_search'])) {
      unset($current_values['keyword_search']);
      $store->set('saved_values', $current_values);
    }

    // Retrieve the current URI and query parameters.
    $current_uri = \Drupal::service('path.current')->getPath();
    $current_query = \Drupal::request()->query->all();

    // Check for 'f' parameter and clean up the 'combined:aluminum'.
    if (isset($current_query['f']) && is_array($current_query['f'])) {
      // Iterate through the array and unset the matching value.
      foreach ($current_query['f'] as $key => $value) {
        if (strpos($value, 'combined:') === 0) {
          unset($current_query['f'][$key]);
        }
      }

      // Clean up if the 'f' array is empty after removing.
      if (empty($current_query['f'])) {
        unset($current_query['f']);
      }
    }

    // Recreate the URL without the cleaned 'f[0]' parameter.
    $url = \Drupal\Core\Url::fromUserInput($current_uri, ['query' => $current_query]);

    // Redirect to the cleaned URL.
    $response = new RedirectResponse($url->toString());
    $response->send();

    // Regardless of rebuild since we're redirecting.
    $form_state->setRebuild(FALSE);
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
  protected function getCredits($credit_category_tid) {
    // Load terms corresponding to the selected credit category.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'credit'); // Ensure querying terms from the 'credit' vocabulary.
    $query->condition('parent', $credit_category_tid); // Fetch child terms directly under the selected credit category.
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
  protected function getRatingSystems($leed_version_tid) {
    // Load terms corresponding to the selected LEED version.
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'credit')->accessCheck(false);

    $query->condition('parent', $leed_version_tid); // Fetch child terms.
    $tids = $query->execute();
    $rating_systems_with_children = [];

    if (!empty($tids)) {
      $child_terms = Term::loadMultiple($tids);
      // Step 2: Check each child term to see if it has further children.
      foreach ($child_terms as $child_term) {
        // Create a query to check for children of this child term.
        $sub_child_query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
        $sub_child_query->condition('parent', $child_term->id())->accessCheck(false);;
        $sub_child_tids = $sub_child_query->execute();

        // If this child term has its own children, add it to the result array.
        if (!empty($sub_child_tids)) {
          $rating_systems_with_children[$child_term->id()] = $child_term->getName();
        }
      }
    }

    // Log the resultant terms for debugging purposes.
    \Drupal::logger('custom_module')->notice('Rating systems with children: @values', ['@values' => $rating_systems_with_children]);

    return $rating_systems_with_children;
  }


  /**
   * Returns a list of taxonomy terms for the location regions.
   *
   * @return array
   *   An associative array of region term IDs and names.
   */
  protected function getLocationRegions() {
    $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('location', 0, 1); // Level 1 terms.
    $regions = [];
    foreach ($tree as $term) {
      $regions[$term->tid] = $term->name;
    }
    return $regions;
  }
  protected function getLeedVersion() {
    $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('credit', 0, 1); // Level 1 terms.
    $leed_version = [];
    foreach ($tree as $term) {
      $leed_version[$term->tid] ='LEED '.$term->name;
    }
    return $leed_version;
  }
  /**
   * Fetches child terms (countries) for a given region term ID.
   *
   * @param int $region_tid
   *   The term ID of the selected region.
   *
   * @return array
   *   An associative array of country term IDs and names.
   */
  protected function getCountriesByRegion($region_tid) {
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', 'location')->accessCheck(false);

    $query->condition('parent', $region_tid); // Fetch child terms.
    $tids = $query->execute();

    $countries = [];
    if (!empty($tids)) {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
      foreach ($terms as $term) {
        $countries[$term->id()] = $term->getName();
      }
    }

    return $countries;
  }

  /**
   * Checks if the term is at the third level of hierarchy.
   *
   * @param int $tid
   *   The term ID to check.
   *
   * @return array
   *   TRUE if the term is at the third level, FALSE otherwise.
   */


  function loadAllParents($tid) {


    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $parents = [];

    if ($term = $term_storage->load($tid)) {
      $parents[] = $term;
      $n = 0;

      while (isset($parents[$n]) && $parent_id = $parents[$n]->parent->target_id) {
        if ($parent = $term_storage->load($parent_id)) {
          $parents[] = $parent;
          $n++;
        } else {
          break;
        }
      }
    }


    return $parents;
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
  function getCreditFilterLabel($tid) {
    $parents = $this->loadAllParents($tid);
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
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Add validation if necessary.
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {
    $store = $this->tempStoreFactory->get('leeduser_search');

    // Initialize the 'f' query parameter as an array.
    $filters = [];

    // Initialize credit variable.
    $credit = null;
    $location= null;
    $keyword_search = $form_state->getValue('keyword_search');
    if (!empty($keyword_search)) {
      $filters[] = 'combined:' . $keyword_search;
    }

    // LEED version.
    $leed_version = $form_state->getValue('leed_version');
    if (!empty($leed_version)) {
      $filters[] = 'credit:' . $leed_version;
      $credit = $leed_version; // Assign credit here if version is not empty
    }

    // Rating system.
    $rating_system = $form_state->getValue('rating_system');
    if (!empty($rating_system)) {
      $filters[] = 'credit:' . $rating_system;
      $credit = $rating_system; // Override previous credit assignment if not empty
    }

    // Credit category.
    $credit_category = $form_state->getValue('credit_category');
    if (!empty($credit_category)) {
      $filters[] = 'credit:' . $credit_category;
      $credit = $credit_category; // Override again if category is not empty
    }

    // Actual Credit.
    $credit_value = $form_state->getValue('credit');
    if (!empty($credit_value)) {
      $filters[] = 'credit:' . $credit_value;
      $credit = $credit_value; // Final hierarchy level, final override
    }

    // Location: region.
    $location_region = $form_state->getValue('location_region');
    if (!empty($location_region)) {
      $filters[] = 'location:' . $location_region;
      $location=$location_region;
    }

    // Location: country.
    $location_country = $form_state->getValue('location_country');
    if (!empty($location_country)) {
      $filters[] = 'location:' . $location_country;
      $location=$location_country;

    }

    // Save the form values to the TempStore.
    $values_to_save = [
      'location' => $location,
      'keyword_search' => $keyword_search,
      'credits' => $credit, // Store the last assigned credit value
    ];
    $store->set('saved_values', $values_to_save);

    // Redirect with the filters appended to the URL.
    $form_state->setRedirect('<current>', [], ['query' => ['f' => $filters]]);
  }}
