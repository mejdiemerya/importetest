<?php

namespace Drupal\leeduser_tour\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides the content for the Leeduser Tour page.
 */
class LeeduserTourController extends ControllerBase {

  /**
   * Returns a renderable array for the Leeduser Tour page.
   */
  public function content() {
    return [
      '#theme' => 'leeduser_tour_template',
      '#title' => $this->t('Welcome to the Leeduser Tour'),
      '#content' => $this->t('This is the content of the Leeduser Tour page. Customize it as needed!'),
    ];
  }

  public function contentstore() {
    return [
      '#theme' => 'store_template',
      '#title' => $this->t('Welcome to the Leeduser Tour'),
      '#content' => $this->t('This is the content of the Leeduser Tour page. Customize it as needed!'),
    ];
  }

}
