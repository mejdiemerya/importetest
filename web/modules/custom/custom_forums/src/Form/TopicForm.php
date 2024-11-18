<?php

namespace Drupal\custom_forums\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

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
    $vocabularies = [
      'leed_version' => 'leed_version',
      'rating_system' => 'rating_system',
      'credit_categorie' => 'credit_categorie',
      'credit' => 'credit',
    ];

// Loop through each vocabulary and create a corresponding select field.
foreach ($vocabularies as $field_name => $vocabulary_id) {
  // Load the terms for the current vocabulary.
  $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary_id);

  // Prepare the options list.
  $options = [];
  foreach ($terms as $term) {
    $options[$term->tid] = $term->name;
  }

  // Add the select field to the form.
  $form[$field_name] = [
    '#type' => 'select',
    '#title' => $this->t('Choose a term from @vocabulary', ['@vocabulary' => $vocabulary_id]),
    '#options' => $options,
    '#required' => TRUE,
  ];
}

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Topic'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {




    $form_state->setRedirect('<front>');
  }
}
