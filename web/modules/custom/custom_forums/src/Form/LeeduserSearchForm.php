<?php

namespace Drupal\custom_forums\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\taxonomy\Entity\Term;
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


    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/jquery';

    $form['keyword_search'] = [
      '#type' => 'textfield',
      '#title' => t('Search Forums'),
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => t('Search within forums'),
      ],
      '#prefix' => '<div id="keyword-search">',
      '#suffix' => '</div>',
    ];
    // Post Question Link.
    $form['post_question'] = [
      '#type' => 'markup',
      '#prefix' => '<a id="post-question" href=""><i class="fi flaticon-communication"></i>',
      '#suffix' => '</a>',
      '#markup' => t('Post a question or comment'),
    ];
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
    }
    $leed_version = $form_state->getValue('leed_version');
    if (!empty($leed_version)) {
      $form['credit_filter_fieldset']['rating_system']['#options'] = $this->getRatingSystems($leed_version);
    }
    $rating_system = $form_state->getValue('rating_system');
    if (!empty($rating_system)) {
      $form['credit_filter_fieldset']['credit_category']['#options'] = $this->getCreditCategories($rating_system);
    }
    $credit_category = $form_state->getValue('credit_category');
    if (!empty($credit_category)) {
      $form['credit_filter_fieldset']['credit']['#options'] = $this->getCreditCategories($credit_category);
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
   * Helper function to get post question link.
   */
  protected function getPostQuestionLink() {
    global $user;

    // Example logic based on Drupal 7 code.
    // Adjust according to Drupal 10 APIs and your site structure.

    // Check user roles and active credit filters.
    if ($this->hasSingleCreditFilter() && $this->currentUserHasPermission()) {
      $credit_id = (int) $this->getActiveCredits()[0];
      $query = [
        'credit_id' => $credit_id,
        'forum_id' => 'BG_LEEDUSER_SEARCH_CREDITS_FORUM_TID',
        'destination' => 'forums',
      ];
      $url = Url::fromRoute('node.add', ['node_type' => 'forum'], ['query' => $query])->toString();
    }
    elseif (isset($this->getCurrentFilters()['im_field_tipsheet'])) {
      $query = [
        'tipsheet_id' => $this->getCurrentFilters()['im_field_tipsheet'],
        'forum_id' => 'BG_LEEDUSER_SEARCH_TIPSHEET_FORUM_TID',
      ];
      $url = Url::fromRoute('node.add', ['node_type' => 'forum'], ['query' => $query])->toString();
    }
    elseif (isset($this->getCurrentFilters()['im_taxonomy_forums'])) {
      $query = [
        'forum_id' => $this->getCurrentFilters()['im_taxonomy_forums'],
      ];
      $url = Url::fromRoute('node.add', ['node_type' => 'forum'], ['query' => $query])->toString();
    }
    else {
      // Use modal window.
      $url = '#';
      // Ensure you have a modal setup using Drupal's AJAX API.
    }

    // Build the link with appropriate attributes.
    $attributes = [];
    if ($url == '#') {
      $attributes['data-toggle'] = 'modal';
      $attributes['data-target'] = '#post-question-modal';
    }

    $link = Link::fromTextAndUrl(
      $this->t('<i class="fi flaticon-communication"></i> Post a question or comment', [], ['context' => 'Html']),
      Url::fromUserInput($url)
    )->toRenderable();

    $link['#attributes']['id'] = 'post-question';

    return $link;
  }





  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Add validation if necessary.
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    // Initialize the 'f' query parameter as an array.
    $filters = [];

    // LEED version.
    $leed_version = $form_state->getValue('leed_version');
    if (!empty($leed_version)) {
      $filters[] = 'credit:' . $leed_version;
    }

    // Rating system.
    $rating_system = $form_state->getValue('rating_system');
    if (!empty($rating_system)) {
      $filters[] = 'credit:' . $rating_system;
    }

    // Credit category.
    $credit_category = $form_state->getValue('credit_category');
    if (!empty($credit_category)) {
      $filters[] = 'credit:' . $credit_category;
    }

    // Credit.
    $credit = $form_state->getValue('credit');
    if (!empty($credit)) {
      $filters[] = 'credit:' . $credit;
    }

    // Location: region.
    $location_region = $form_state->getValue('location_region');
    if (!empty($location_region)) {
      $filters[] = 'location:' . $location_region;
    }

    // Location: country.
    $location_country = $form_state->getValue('location_country');
    if (!empty($location_country)) {
      $filters[] = 'location:' . $location_country;
    }

    // Redirect with the filters appended to the URL.
    $form_state->setRedirect('<current>', [], ['query' => ['f' => $filters]]);
  }
}
