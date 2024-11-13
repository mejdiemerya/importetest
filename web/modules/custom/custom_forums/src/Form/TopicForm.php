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
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Topic Title'),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
    ];

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
    // Create a node of type "topic".
    $node = Node::create([
      'type' => 'topic',
      'title' => $form_state->getValue('title'),
      'body' => [
        'value' => $form_state->getValue('description'),
        'format' => 'basic_html',
      ],
    ]);

    // Save the node.
    $node->save();

    $this->messenger()->addMessage($this->t('Topic "%title" has been created.', ['%title' => $node->getTitle()]));
    $form_state->setRedirect('<front>');
  }
}
