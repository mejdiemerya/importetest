<?php

namespace Drupal\custom_forums\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CustomForumsSettingsForm extends ConfigFormBase
{
  public function getFormId()
  {
    return 'custom_forums_settings';
  }

  protected function getEditableConfigNames() {
    return ['custom_forums.settings'];
  }
  public function buildForm(array $form, FormStateInterface $form_state)
  {


    $form['well_tipsheet_name'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Well Tipsheet Node Name'),
      '#description' => $this->t('Enter the node name for tipsheets, separated by commas.'),
      '#default_value' => $this->config('custom_forums.settings')->get('well_tipsheet_name'),
      '#rows' => 5,
    ];
    return parent::buildForm($form, $form_state);

  }

    public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $config = $this->config('custom_forums.settings');
    $config->set('well_tipsheet_name', $form_state->getValues()['well_tipsheet_name']);
    $config->save();
    parent::submitForm($form, $form_state);

  }




}
