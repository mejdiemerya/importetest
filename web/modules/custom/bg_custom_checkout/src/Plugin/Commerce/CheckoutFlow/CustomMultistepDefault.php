<?php
namespace Drupal\bg_custom_checkout\Plugin\Commerce\CheckoutFlow;

use Drupal\Commerce\Plugin\Commerce\CheckoutFlow\MultistepDefault;

/**
 * Extends the default multistep checkout flow plugin.
 */
class CustomMultistepDefault extends \Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\MultistepDefault {
  /**
   * {@inheritdoc}
   */
  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    // Note that previous_label and next_label are not the labels
    // shown on the step itself. Instead, they are the labels shown
    // when going back to the step, or proceeding to the step.
    return [
        'login' => [
          'label' => $this->t('Log in'),
          'previous_label' => $this->t('Go back'),
          'has_sidebar' => true,
        ],
        'order_information' => [
          'label' => $this->t('Order information'),
          'has_sidebar' => TRUE,
          'previous_label' => $this->t('Go back'),
        ],
        'review' => [
          'label' => $this->t('Review'),
          'next_label' => $this->t('Continue to review'),
          'previous_label' => $this->t('Go back'),
          'has_sidebar' => TRUE,
        ],
      ] + parent::getSteps();
  }
}

