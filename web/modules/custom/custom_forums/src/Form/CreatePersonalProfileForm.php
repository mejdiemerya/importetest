<?php

namespace Drupal\custom_forums\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;


/**
 * Provides a Basic Membership Signup form.
 */


class CreatePersonalProfileForm extends FormBase {

  public function getFormId() {
    return 'create_personal_profile_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div class="create-personal-profile row">';
    $form['#suffix'] = '</div>';



    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
      '#description' => $this->t('Use your firm or campus email (e.g., you@yourfirm.com or you@yourcampus.edu).'),
    ];

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => TRUE,
      '#attributes' => ['autocomplete' => 'given-name'],
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#attributes' => ['autocomplete' => 'family-name'],
    ];

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Create a Password'),
      '#required' => TRUE,
    ];

    $form['confirm_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Verify Password'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create profile'),
      '#attributes' => ['class' => ['btn', 'btn-warning']],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $password = $form_state->getValue('password');
    $confirm_password = $form_state->getValue('confirm_password');
    if ($password !== $confirm_password) {
      $form_state->setErrorByName('confirm_password', $this->t('Passwords do not match.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $first_name = $form_state->getValue('first_name');
    $last_name = $form_state->getValue('last_name');


    $this->messenger()->addStatus($this->t('Profile created successfully for %name', ['%name' => $first_name . ' ' . $last_name]));
  }
}

