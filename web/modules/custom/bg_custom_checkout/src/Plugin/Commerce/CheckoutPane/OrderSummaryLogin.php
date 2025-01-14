<?php

namespace Drupal\bg_custom_checkout\Plugin\Commerce\CheckoutPane;


use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;


/**
 * Provides the custom checkout pane.
 *
 * @CommerceCheckoutPane(
 *   id = "order_summary_login",
 *   label = @Translation("Order summary login"),
 * )
 */
class OrderSummaryLogin extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'view' => '',
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    $parent_summary = parent::buildConfigurationSummary();
    if ($this->configuration['view']) {
      $view_storage = $this->entityTypeManager->getStorage('view');
      $view = $view_storage->load($this->configuration['view']);
      if ($view) {
        $summary = $this->t('View: @view', ['@view' => $view->label()]);
        return $parent_summary ? implode('<br>', [$parent_summary, $summary]) : $summary;
      }

      return $parent_summary;
    }
    else {
      $summary = $this->t('View: Not used');
      return $parent_summary ? implode('<br>', [$parent_summary, $summary]) : $summary;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['use_view'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use a View to display the order summary'),
      '#description' => $this->t('Overrides the checkout order summary template with the output of a View.'),
      '#default_value' => !empty($this->configuration['view']),
    ];

    $view_storage = $this->entityTypeManager->getStorage('view');
    $available_summary_views = [];
    /** @var \Drupal\views\Entity\View $view */
    foreach ($view_storage->loadMultiple() as $view) {
      if (strpos($view->get('tag'), 'commerce_order_summary') !== FALSE) {
        $available_summary_views[$view->id()] = $view->label();
      }
    }

    $form['view'] = [
      '#type' => 'select',
      '#title' => $this->t('View'),
      '#options' => $available_summary_views,
      '#default_value' => $this->configuration['view'],
      '#required' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="configuration[panes][order_summary][configuration][use_view]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['view'] = NULL;
      if ($values['use_view']) {
        $this->configuration['view'] = $values['view'];
      }
    }
  }
  public function isVisible() {
    return \Drupal::currentUser()->isAnonymous();
  }
  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($this->configuration['view']) {
      $pane_form['summary'] = [
        '#type' => 'view',
        '#name' => $this->configuration['view'],
        '#display_id' => 'default',
        '#arguments' => [$this->order->id()],
        '#embed' => TRUE,
      ];
    }
    else {
      $pane_form['summary'] = [
        '#theme' => 'commerce_checkout_order_summary',
        '#order_entity' => $this->order,
        '#checkout_step' => $complete_form['#step_id'],
      ];
    }

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {}

}
