<?php

namespace Drupal\custom_forums\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

// Retrieve selected taxonomy term IDs from the form state.
    $selected_terms = [];
    foreach ($form_state->getValues() as $field_name => $value) {
      // Check if the field is a term selection.
      if (in_array($field_name, ['leed_version', 'rating_system', 'credit_categorie', 'credit'])) {
        // Add the selected term ID to the array.
        $selected_terms[$field_name] = $value;
      }
    }
    $query = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery()->accessCheck(false);
    $query->condition('vid', 'forums')
      ->condition('field_credit',$selected_terms['credit'])
      ->condition('field_credit_category',$selected_terms['credit_categorie'])
      ->condition('field_leed_version',$selected_terms['leed_version'])
      ->condition('field_rating_system',$selected_terms['rating_system']);
    $tids = $query->execute();
    $tid=reset($tids);
    $redirect_url= Url::fromRoute('node.add',['node_type' => 'forum'])->setOption('query', ['forum_id' => $tid])->toString();;

    // Perform the redirect.
    return  new RedirectResponse($redirect_url);

  }
}
