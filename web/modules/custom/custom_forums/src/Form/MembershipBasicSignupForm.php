<?php

namespace Drupal\custom_forums\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;


/**
 * Provides a Basic Membership Signup form.
 */


class MembershipBasicSignupForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'membership_basic_signup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $step = $form_state->get('step') ?? 1;
    $step_request = \Drupal::request()->request->get('step');

    if (!empty($step_request)) {
      $step = (int) $step_request;
      $form_state->set('step', $step);
    }
    
    $form['selector'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row']],
      '#prefix' => '<div id="signup-form-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['selector']['col-1'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['col-md-6 col-lg-7']],
    ];
    if ($step == 1) {
      $form['selector']['col-1']['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email Address'),
        '#required' => TRUE,
      ];
      $form['selector']['col-1']['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
      ];
      $form['selector']['info_block'] = [
        '#theme' => 'email_safety_message',
      ];
    }
    elseif ($step == 2) {
      $email = \Drupal::service('tempstore.private')->get('signup')->get('email');
      $existing_users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $email]);

      if ($existing_users) {
        $form['selector']['col-1']['message'] = [
          '#markup' => '<p><strong>You already have a BuildingGreen account — please enter your password:</strong></p>',
        ];
        $form['selector']['col-1']['password'] = [
          '#type' => 'password',
          '#title' => $this->t('Password'),
          '#required' => TRUE,
        ];
        $form['selector']['col-1']['login'] = [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
        ];
        $form['selector']['col-1']['forgot_password'] = [
          '#markup' => '<a href="#" id="forgot-password-link">' . $this->t('Forgot password?') . '</a>',
        ];
        $form['#attached']['library'][] = 'custom_forums/forgot_password';
      } else {
        $form['selector']['col-1']['first_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('First Name'),
          '#required' => TRUE,
        ];
        $form['selector']['col-1']['last_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Last Name'),
          '#required' => TRUE,
        ];
        $form['selector']['col-1']['password'] = [
          '#type' => 'password',
          '#title' => $this->t('Create a Password'),
          '#required' => TRUE,
        ];
        $form['selector']['col-1']['confirm_password'] = [
          '#type' => 'password',
          '#title' => $this->t('Verify Password'),
          '#required' => TRUE,
        ];
        $form['selector']['col-1']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Next'),
        ];
      }
    }
    elseif ($step == 3) {
      $email = \Drupal::service('tempstore.private')->get('signup')->get('email');
      $form['selector']['col-1']['info'] = [
        '#markup' => $this->t('We will send you to send a password reset link to <strong>@email</strong>.', ['@email' => $email]),
      ];
      $form['selector']['col-1']['reset_password'] = [
        '#type' => 'submit',
        '#value' => $this->t('Request pasword reset'),
        '#submit' => ['::resetPasswordSubmit'],
      ];
    }
    elseif ($step == 'success') {
      $form['selector']['col-1']['sucess'] = [
        '#theme' => 'sucess_signup',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $step = $form_state->get('step') ?? 1;

    if ($step == 1) {
      $email = $form_state->getValue('email');
      \Drupal::service('tempstore.private')->get('signup')->set('email', $email);

      $existing_users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $email]);

      if ($existing_users) {
        $form_state->set('step', 2);
      } else {
        $form_state->set('step', 2);
      }
      $form_state->setRebuild();
    }
    elseif ($step == 2) {
      $email = \Drupal::service('tempstore.private')->get('signup')->get('email');
      $existing_users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $email]);

      if ($existing_users) {
        $user = reset($existing_users);
        $password = $form_state->getValue('password');

        if (\Drupal::service('password')->check($password, $user->getPassword())) {
          user_login_finalize($user);
          $form_state->setRedirect('<front>');
        } else {
          $form_state->set('step', 3);
          $form_state->setRebuild();
        }
      } else {
        $first_name = $form_state->getValue('first_name');
        $last_name = $form_state->getValue('last_name');
        $password = $form_state->getValue('password');
        $confirm_password = $form_state->getValue('confirm_password');

        if ($password !== $confirm_password) {
          $form_state->setErrorByName('confirm_password', $this->t('Passwords do not match.'));
          return;
        }

        $base_username = explode('@', $email)[0];
        $existing_users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['name' => $base_username]);

        if ($existing_users) {
          $i = 1;
          $new_username = $base_username . $i;

          while (\Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['name' => $new_username])) {
            $i++;
            $new_username = $base_username . $i;
          }
          $base_username = $new_username;
        }
        $user = User::create([
          'name' => $base_username,
          'mail' => $email,
          'field_first_name' => $first_name,
          'field_last_name' => $last_name,
          'roles' => ['lu_basic'],
          'status' => 1,
        ]);
        $user->setPassword($password);
        $user->save();
       _user_mail_notify('register_no_approval_required', $user);
        $form_state->set('step', 'success');
        $form_state->setRebuild();

      }
    }
    elseif ($step == 3) {
      \Drupal::messenger()->addMessage($this->t('Your reset password email has been sent.'));
    }
  }

  /**
   * Custom submit handler for sending reset password email.
   */
  public function resetPasswordSubmit(array &$form, FormStateInterface $form_state) {
    $email = \Drupal::service('tempstore.private')->get('signup')->get('email');
    $existing_users = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['mail' => $email]);

    if ($existing_users) {
      $user = reset($existing_users);
      _user_mail_notify('password_reset', $user);
      \Drupal::messenger()->addStatus($this->t('A password reset email has been sent to %email.', ['%email' => $email]));
    }

    $form_state->setRedirect('<front>');
  }

}


