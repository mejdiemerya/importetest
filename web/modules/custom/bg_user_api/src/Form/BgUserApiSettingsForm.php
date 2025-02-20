<?php

namespace Drupal\bg_user_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure API settings for BG User API.
 */
class BgUserApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bg_user_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bg_user_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bg_user_api.settings');

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL'),
      '#default_value' => $config->get('api_url'),
      '#required' => TRUE,
    ];

    $form['api_user'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API User'),
      '#default_value' => $config->get('api_user'),
      '#required' => TRUE,
    ];

    $form['api_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Password'),
      '#default_value' => $config->get('api_password'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('bg_user_api.settings')
      ->set('api_url', $form_state->getValue('api_url'))
      ->set('api_user', $form_state->getValue('api_user'))
      ->set('api_password', $form_state->getValue('api_password'))
      ->save();
  }

}
