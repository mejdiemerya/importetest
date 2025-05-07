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
    $current_user = \Drupal::currentUser();

    $store = $this->tempStoreFactory->get('leeduser_search'); // Adjust namespace as needed.
    // Attempt to retrieve saved values if they exist.
    $saved_values = $store->get('saved_values') ?: [];
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/jquery';
    $form['#attached']['library'][]='core/drupal.dialog';
    $form['#attached']['library'][]='custom_forums/submit_on_enter';
    // Get the current request
    $request = \Drupal::request();
// Retrieve query parameters from the current URL
    $query_params = $request->query->all();
// Initialize variables to store extracted IDs
    $forum_id = null;
    $credit_id = [];
    $location_id = [];
    $tipsheet_id= null;
// Check if the 'f' parameter contains multiple items.
    if (isset($query_params['f'])) {
      // Loop through each parameter to extract key-value pairs.
      foreach ($query_params['f'] as $param) {
        if (preg_match('/^forum:(\d+)$/', $param, $matches)) {
          $forum_id = $matches[1];
        } elseif (preg_match('/^credit:(\d+)$/', $param, $matches)) {
          $credit_id [] = $matches[1];
        } elseif (preg_match('/^location:(\d+)$/', $param, $matches)) {
          $location_id [] = $matches[1];
        }
        elseif (preg_match('/^tipsheet:(\d+)$/', $param, $matches)) {
          $tipsheet_id = $matches[1];
        }
      }
    }
    $active_search_parents=[];
    if (!empty($saved_values['credits']) && count($saved_values['credits']) === 1) {
      $active_search_parents = $this->loadAllParents($saved_values['credits'][0]);
    }
    if ((count($active_search_parents) === 4 )&&
      $current_user->isAuthenticated() &&
      empty(array_intersect(['bg_og_multiuser', 'lu_og_multiuser'], $current_user->getRoles()))
    ){
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

    $form['container_search'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['search-group', 'position-relative']],
    ];
    $form['container_search']['clear_search'] = array(
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
    $form['container_search']['keyword_search'] = [
      '#type' => 'textfield',
      '#title' => t('Search Forums'),
      '#default_value' => isset($saved_values['keyword_search']) ? $saved_values['keyword_search'] : '',
      '#attributes' => [
        'placeholder' => t('Search within forums'),
        'id' => 'keyword-search-field',
      ],
      '#prefix' => '<div id="keyword-search">',
      '#suffix' => '</div>',
    ];
    // Display active location filters if they exist.
    if (!empty($saved_values['location'])|| !empty($location_id)) {
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
      $all_filters = array_unique(array_merge(
        $saved_values['location'] ?? [],
        $location_id ?? []
      ));

      foreach ($all_filters as $filter) {

          if ($term = Term::load($filter)) {
            $form['active_location_filter_fieldset']['location_queries']['saved_query_location_' . $term->id()] = [
              '#type' => 'checkbox',
              '#title' => $term->getName(),
              '#description' => $term->getDescription(),
              '#default_value' => 1,
              '#prefix' => '<div id="saved_query_location_' . $term->id() . '">',
              '#suffix' => '</div>',
            ];
          }

      }
    }
    if (!empty(($saved_values['credits'])) || !empty($credit_id)) {
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
      $all_filters = array_unique(array_merge(
        $saved_values['credits'] ?? [],
          $credit_id ?? []
      ));
        foreach ($all_filters as $filter) {
            if ($label = getCreditFilterLabel($filter)) {
            $form['active_credit_filter_fieldset']['queries']['saved_query_credit_' . $filter] = array(
              '#type' => 'checkbox',
              '#title' => $label['title'],
              '#suffix' => '<div class="credit-query-description">' . htmlspecialchars($label['description'], ENT_QUOTES, 'UTF-8') . '</div>',
              '#default_value' => 1,
            );
            }
        }
    }

    $leed_version = $form_state->getValue('leed_version');  // Ensure the variable is initialized
    $rating_system = $form_state->getValue('rating_system');
    $credit_category =  $form_state->getValue('credit_category');
    $form['credit_filter_fieldset'] = array(
      '#type' => 'container',
      '#title' =>  t('Filter by LEED Credit'),
      '#prefix' => '<div id="credit-filter-wrapper">',
      '#suffix' => '</div>',
    );
    $form['credit_filter_fieldset']['leed_version'] = [
      '#type' => 'select',
      '#empty_option' => t('Choose a LEED version'),
      '#options' =>$this->getLeedVersion(),
      '#prefix' => '<div id="leed_version">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::updateRatingSystems',
        'wrapper' => 'credit-filter-wrapper',
      ],
    ];
    $form['credit_filter_fieldset']['rating_system'] = [
      '#prefix' => '<div id="rating_system">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#empty_option' => t('Rating system (optional)'),
      '#options' => !empty($leed_version) ? $this->getRatingSystemOptions($leed_version) : [],
      '#disabled' => empty($leed_version),
      '#ajax' => [
        'callback' => '::updateCreditCategories',
        'wrapper' => 'credit-filter-wrapper',
      ],
    ];
    $form['credit_filter_fieldset']['credit_category'] = [
      '#prefix' => '<div id="credit_category">',
      '#suffix' => '</div>',
      '#type' => 'select',
      '#empty_option' =>  t('Credit category (optional)'),
      '#options' => !empty($rating_system) ? $this->getCreditCategoriesOptions($rating_system) : [],
      '#disabled' => empty($rating_system),
      '#ajax' => [
        'callback' => '::updateCredits',
        'wrapper' => 'credit-filter-wrapper',
      ],
    ];

    $form['credit_filter_fieldset']['credit'] = array(
      '#type' => 'select',
      '#prefix' => '<div id="credit">',
      '#suffix' => '</div>',
      '#empty_option' =>  t('Credit (optional)'),
      '#options' => !empty($credit_category) ? $this->getCreditsOptions($credit_category) : [],
      '#disabled' => empty($credit_category),
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
      $form['credit_filter_fieldset']['rating_system']['#options'] = $this->getRatingSystemOptions($leed_version);
      unset($form['credit_filter_fieldset']['rating_system']['#attributes']['disabled']);

    }
    $rating_system = $form_state->getValue('rating_system');
    if (!empty($rating_system)) {
      $form['credit_filter_fieldset']['credit_category']['#options'] = $this->getCreditCategoriesOptions($rating_system);
      unset($form['credit_filter_fieldset']['credit_category']['#attributes']['disabled']);

    }
    $credit_category = $form_state->getValue('credit_category');
    if (!empty($credit_category)) {
      $form['credit_filter_fieldset']['credit']['#options'] = $this->getCreditsOptions($credit_category);
      unset($form['credit_filter_fieldset']['credit']['#attributes']['disabled']);

    }
    return $form;
  }
  public function updateCreditCategories(array &$form, FormStateInterface $form_state) {
    // Get the selected rating system.
    $selected_rating_system = $form_state->getValue('rating_system');

    // Fetch the corresponding credit categories.
    $credit_categories = $this->getCreditCategoriesOptions($selected_rating_system);

    // Set the options for the credit category select list.
    $form['credit_filter_fieldset']['credit_category']['#options'] =[''=>t('Credit category (optional)')] + $credit_categories;
    $form['credit_filter_fieldset']['credit']['#options'] = ['' => t('Credit(optional)')];
    $form['credit_filter_fieldset']['credit']['#attributes']['disabled'] = 'disabled';
    $form_state->setRebuild(TRUE);

    // Return the updated credit category part of the form.
    return $form['credit_filter_fieldset'];
  }
  public function updateCredits(array &$form, FormStateInterface $form_state) {
    // Get the selected credit category.
    $selected_credit_category = $form_state->getValue('credit_category');

    // Fetch the corresponding credits.
    $credits = $this->getCreditsOptions($selected_credit_category);

    // Set the options for the credit select list.
    $form['credit_filter_fieldset']['credit']['#options'] =[''=>t('Credit (optional)')] + $credits;
    $form_state->setRebuild(TRUE);

    // Return the updated credit part of the form.
    return $form['credit_filter_fieldset'];
  }
  public function updateRatingSystems(array &$form, FormStateInterface $form_state) {
    $selected_leed_version = $form_state->getValue('leed_version');

    // Fetch the rating systems based on selected LEED version.
    $rating_systems = $this->getRatingSystemOptions($selected_leed_version);

    $form['credit_filter_fieldset']['rating_system']['#options'] =['' => t('Rating system (optional)')] +  $rating_systems;
    $form['credit_filter_fieldset']['credit_category']['#options'] = ['' => t('Credit category (optional)')];
    $form['credit_filter_fieldset']['credit_category']['#attributes']['disabled'] = 'disabled';
    $form['credit_filter_fieldset']['credit']['#options'] = ['' => t('Credit (optional)')];

    $form['credit_filter_fieldset']['credit']['#attributes']['disabled'] = 'disabled';

    $form_state->setRebuild(TRUE);

    return $form['credit_filter_fieldset'];
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

    if (isset($current_values['location'])) {
      unset($current_values['location']);
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
    // Check for the 'search_api_fulltext' parameter and clean it up.
    if (isset($current_query['search_api_fulltext'])) {
      unset($current_query['search_api_fulltext']);
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
    // Get the database connection.
    $connection = \Drupal::database();

    // Prepare a select query on the `taxonomy_term__parent` table.
    $query = $connection->select('taxonomy_term__parent', 'parent');

    // Join with `taxonomy_term_field_data` to get term name.
    $query->join('taxonomy_term_field_data', 'term_data', 'parent.entity_id = term_data.tid');

    // Select fields.
    $query->fields('term_data', ['tid', 'name']);

    // Add conditions to the query.
    $query->condition('parent.parent_target_id', $rating_system_tid);  // Using provided $rating_system_tid.
    $query->condition('term_data.vid', 'credit');  // Assuming 'credit' is your vocab ID as specified initially.

    // Execute the query and fetch results.
    $result = $query->execute();
    $credit_categories = [];

    // Process each record.
    foreach ($result as $record) {
      $credit_categories[$record->tid] = $record->name;
    }

    return $credit_categories;
  }
  protected function getCreditCategoriesOptions($leed_version) {
    $options = [];
    if (!empty($leed_version)) {
      $rating_systems = $this->getCreditCategories($leed_version);
      foreach ($rating_systems as $group_label => $group_items) {
        $label=$this->getCreditFilterLabel($group_label);
        $options[$group_items] = [
          $group_label =>$label['description'] ,
        ];
      }
    }
    return $options;
  }

  protected function getCredits($credit_category_tid) {
    // Get the database connection.
    $connection = \Drupal::database();

    // Prepare a select query on the `taxonomy_term__parent` table.
    $query = $connection->select('taxonomy_term__parent', 'parent');

    // Join with `taxonomy_term_field_data` to get term name.
    $query->join('taxonomy_term_field_data', 'term_data', 'parent.entity_id = term_data.tid');

    // Select fields.
    $query->fields('term_data', ['tid', 'name']);

    // Add conditions to the query.
    $query->condition('parent.parent_target_id', $credit_category_tid); // Using provided $credit_category_tid.
    $query->condition('term_data.vid', 'credit'); // Assuming 'credit' is your vocabulary ID.

    // Execute the query and fetch results.
    $result = $query->execute();
    $credits = [];

    // Process each record.
    foreach ($result as $record) {
      $credits[$record->tid] = $record->name;
    }

    return $credits;
  }
  protected function getCreditsOptions($leed_version) {
    $options = [];
    if (!empty($leed_version)) {
      $rating_systems = $this->getCredits($leed_version);
      foreach ($rating_systems as $group_label => $group_items) {
        $label=$this->getCreditFilterLabel($group_label);
        $options[$group_items] = [
          $group_label =>$label['description'] ,
        ];
      }
    }
    return $options;
  }
  protected function getRatingSystems($leed_version_tid) {
    // Get the database connection.
    $connection = \Drupal::database();

    // Prepare a select query to find child term IDs of the provided LEED version.
    $query = $connection->select('taxonomy_term__parent', 'parent');
    $query->addField('parent', 'entity_id', 'child_tid');
    $query->join('taxonomy_term_field_data', 'term_data', 'parent.entity_id = term_data.tid');
    $query->fields('term_data', ['name']);
    $query->condition('parent.parent_target_id', $leed_version_tid);
    $query->condition('term_data.vid', 'credit');

    // Execute the query and fetch child terms with basic details.
    $child_terms = $query->execute()->fetchAllAssoc('child_tid');

    $rating_systems_with_children = [];

    // Prepare another query to check for further children directly.
    if (!empty($child_terms)) {
      $query = $connection->select('taxonomy_term__parent', 'sub_parent');
      $query->fields('sub_parent', ['parent_target_id']);
      $query->condition('sub_parent.parent_target_id', array_keys($child_terms), 'IN');

      // Execute the query to find which of these child terms have children.
      $sub_child_term_tids = $query->execute()->fetchCol();

      foreach ($sub_child_term_tids as $sub_child_tid) {
        // Use the sub_child_tid to access the name of the parent child term.
        //$rating_systems_with_children[$sub_child_tid] = '<b>'.$child_terms[$sub_child_tid]->name.'</b><p> '.$child_terms[$sub_child_tid]->name.'</p>';

        $rating_systems_with_children[$sub_child_tid] = $child_terms[$sub_child_tid]->name;
      }
    }

    return $rating_systems_with_children;
  }
  protected function getRatingSystemOptions($leed_version) {
    $options = [];
    if (!empty($leed_version)) {
      $rating_systems = $this->getRatingSystems($leed_version);
      foreach ($rating_systems as $group_label => $group_items) {
        $label=$this->getCreditFilterLabel($group_label);
        $options[$group_items] = [
          $group_label =>$label['description'] ,
        ];
      }
    }
    return $options;
  }

  /**
   * Returns a list of taxonomy terms for the location regions.
   *
   * @return array
   *   An associative array of region term IDs and names.
   */
  protected function getLocationRegions() {
    // Load the tree of Level 1 terms (regions).
    $tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('location', 0, 1); // Level 1 terms.
    $countries_with_children = [];

    // Step 1: Loop through the regions (Level 1 terms).
    foreach ($tree as $child_term) {
      // Create a query to check for children of this child term.
      $sub_child_query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery();
      $sub_child_query->condition('parent', $child_term->tid)->accessCheck(false);
      $sub_child_tids = $sub_child_query->execute();

      // If this child term has its own children, load the full term and add it to the result array.
      if (!empty($sub_child_tids)) {
        $full_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($child_term->tid);
        $countries_with_children[$full_term->id()] = $full_term->getName(); // Get the term name using the full term entity.
      }
    }

    return $countries_with_children;
  }
  protected function getLeedVersion() {
    $versions = [
      'v4.1' => 'LEED v4.1',
      'v4' => 'LEED v4',
      'v2009' => 'LEED 2009'
    ];

    $leed_version = [];
    foreach ($versions as $name => $label) {
      $termId = $this->getTermIdByName($name, 'credit');
      if ($termId !== null) {
        $leed_version[$termId] = $label;
      }
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
    // Start timing.
    $start_time = microtime(true);

    // Your database query logic here.
    $connection = \Drupal::database();
    $query = $connection->select('taxonomy_term__parent', 'parent');
    $query->join('taxonomy_term_field_data', 'term_data', 'parent.entity_id = term_data.tid');

    $query->fields('term_data', ['tid', 'name']);
    $query->condition('parent.parent_target_id', $region_tid);
    $query->condition('term_data.vid', 'location');

    $results = $query->execute();
    $countries = [];

    foreach ($results as $record) {
      $countries[$record->tid] = $record->name;
    }

    // End timing.
    $end_time = microtime(true);

    // Calculate the difference.
    $execution_time = $end_time - $start_time;

    // Log the execution time or print it.
    \Drupal::logger('custom_module')->notice('Execution time1: ' . $execution_time . ' seconds');
    // Or: print 'Execution time: ' . $execution_time . ' seconds';

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
        if (isset($parents[0]->description) && !empty($parents[0]->description->value)) {
          $data['description'] = strip_tags($parents[0]->description->value);
        }
        break;

      case 3:
        $data['title'] = strip_tags(sprintf('%s %s', $parents[1]->name->value, $parents[0]->name->value));
        if (isset($parents[0]->description) && !empty($parents[0]->description->value)) {
          $data['description'] = strip_tags($parents[0]->description->value);
        }
        break;

      case 4:
        $data['title'] = strip_tags(sprintf('%s %s', $parents[2]->name->value, $parents[0]->name->value));
        if (isset($parents[0]->description) && !empty($parents[0]->description->value)) {
          $data['description'] = strip_tags($parents[0]->description->value);
        }
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
    $credit = null;
    $location= null;

    // Initialize credit variable.
    $credits = [];
    $locations= [];
    // Get the current request
    $request = \Drupal::request();
// Retrieve query parameters from the current URL
    $query_params = $request->query->all();

    if (isset($query_params['f'])) {
      foreach ($query_params['f'] as $param) {
       if (preg_match('/^credit:(\d+)$/', $param, $matches)) {
         $filters[] = 'credit:' . $matches[1];
         $credits[]=$matches[1];
        }
        if (preg_match('/^location:(\d+)$/', $param, $matches)) {
          $filters[] = 'location:' . $matches[1];
          $locations[]=$matches[1];
        }
      }
    }
    $keyword_search = $form_state->getValue('keyword_search');
    if (!empty($keyword_search)) {
      $search_api_fulltext = $keyword_search;
    }

    // LEED version.
    $leed_version = $form_state->getValue('leed_version');
    if (!empty($leed_version)) {
      $credit = $leed_version; // Assign credit here if version is not empty
    }

    // Rating system.
    $rating_system = $form_state->getValue('rating_system');
    if (!empty($rating_system)) {
      $credit = $rating_system; // Override previous credit assignment if not empty
    }

    // Credit category.
    $credit_category = $form_state->getValue('credit_category');
    if (!empty($credit_category)) {
      $credit = $credit_category; // Override again if category is not empty
    }

    // Actual Credit.
    $credit_value = $form_state->getValue('credit');
    if (!empty($credit_value)) {
      $credit = $credit_value; // Final hierarchy level, final override
    }
if(!empty($credit)){
  $credits[]=$credit;
  $filters[] = 'credit:' . $credit;
}


    // Location: region.
    $location_region = $form_state->getValue('location_region');
    if (!empty($location_region)) {
      $location=$location_region;
    }

    // Location: country.
    $location_country = $form_state->getValue('location_country');
    if (!empty($location_country)) {
      $location=$location_country;
    }
    if(!empty($location)){
      $locations[]=$location;
      $filters[] = 'location:' . $location;
    }

    // Save the form values to the TempStore.
    $values_to_save = [
      'location' => $locations,
      'keyword_search' => $keyword_search,
      'credits' => $credits, // Store the last assigned credit value
    ];
    $store->set('saved_values', $values_to_save);
    $query_params = [
      'f' => $filters,
    ];

    if (!empty($search_api_fulltext)) {
      $query_params['search_api_fulltext'] = $search_api_fulltext;
    }
    // Redirect with the filters appended to the URL.
    $form_state->setRedirect('<current>', [], ['query' => $query_params]);
  }}

