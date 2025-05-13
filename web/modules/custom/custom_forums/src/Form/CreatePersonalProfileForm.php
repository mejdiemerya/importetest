<?php

namespace Drupal\custom_forums\Form;


use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\og\Entity\OgMembership;
use Drupal\og\Og;
use Drupal\og\OgMembershipInterface;
use Drupal\user\Entity\User;
use function Symfony\Component\String\u;


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
    $current_user = \Drupal::currentUser();

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
      '#default_value' => $current_user->isAuthenticated() ? $current_user->getEmail() : '',
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
    $email = $form_state->getValue('email');
    $password = $form_state->getValue('password');
    $confirm_password = $form_state->getValue('confirm_password');
    if ($password !== $confirm_password) {
      $form_state->setErrorByName('confirm_password', $this->t('Passwords do not match.'));
    }
    $data = _account_retrieve($email);
    $email_exists = $data['account_exists'];
    $group_info = $data['group'];

    if (!$email_exists) {
      if (!$group_info) {
        $form_state->setErrorByName('email', $this->t('This email is not valid to join a group.'));
      }
    }

    $form_state->set('account_data', $data);
  }




  public function submitForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $first_name = $form_state->getValue('first_name');
    $last_name = $form_state->getValue('last_name');
    $password = $form_state->getValue('password');

    $data = $form_state->get('account_data');
    $group_info = $data['group'];
    $email_exists = $data['account_exists'];

    if (!$email_exists)
    {
      if ($group_info) {
        $user= _account_create($email, $password, $first_name, $last_name);

        if ($user) {
          $group_id = $group_info['group_id'];

          $group = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->load($group_id);
          $membership = OgMembership::create([
            'type' => 'default',
            'entity_type' => $group->getEntityType()->id(),
            'entity_bundle' => $group->bundle(),
            'entity_id' => $group->id(),
            'uid' => $user['uid'],
            'state' => OgMembershipInterface::STATE_ACTIVE,
          ]);

          $membership->save();
          $title = $group->getTitle();
          if ( strpos($title, 'BG') !== FALSE) {
            $user=  user::load($user['uid']);
            if (!in_array('bg_premium_og', $user->getRoles())) {
              $user->addRole('bg_premium_og');
              $user->save();
            }
          }

          elseif ( strpos($title, 'LU') !== FALSE) {
            $user=  user::load($user['uid']);
            if (!in_array('lu_premium_og', $user->getRoles())) {
              $user->addRole('lu_premium_og');
              $user->save();
            }
          }
        }
      }
    }
    else{
      $this->messenger()->addStatus( 'email exists.');
      $form_state->setRedirect('user.login', [], [
        'query' => ['name' => $email],
      ]);
    }

  }
}

function group_allowed_domain_check($domain) {
  $domain = str_replace('@', '', $domain);
  $query = \Drupal::entityTypeManager()->getStorage('user')->getQuery()
  ->condition('status', 1)->condition('field_allowed_domains', $domain);
  $query->accessCheck(false)
  ->range(0, 1);
  $parent_uid = $query->execute();


  if ($parent_uid) {

    $parent_user = User::load(reset($parent_uid));
    $memberships = Og::getMemberships($parent_user);
    if ($memberships) {
      foreach ($memberships as $membership) {
        $group = $membership->getGroup();
        if ($group && $group->status->value == 1) {
          return [
            'group_id' => $group->id(),
            'group_name' => $parent_user->field_organization->value,
          ];
        }
      }

    }
  }

  return FALSE;
}
function _account_retrieve($mail) {


  $users = \Drupal::entityTypeManager()->getStorage('user')
    ->loadByProperties([
      'mail' => $mail,
    ]);
  $user = reset($users);

  $group_allowing_domain = group_allowed_domain_check(substr($mail, strpos($mail, '@')));

  $account_exists = ($user) ? TRUE : FALSE;

  return [
    'account_exists' => $account_exists,
    'group' => $group_allowing_domain,
  ];
}
function _account_create($email, $password, $first_name, $last_name) {

  $exists = _account_retrieve(($email));
  if ($exists['account_exists']) {
    return ['account_created' => FALSE];
  }

  $query = \Drupal::entityTypeManager()->getStorage('user')->getQuery()
    ->condition('mail', $email);
  $query->accessCheck(false)
    ->range(0, 1);
  $found = $query->execute();

  if ($found ) {
    return ['account_created' => FALSE];
  }

  $new_name = preg_replace('/@.*$/', '', $email);
  $new_name = email_registration_cleanup_username($new_name);
  $new_name = email_registration_unique_username($new_name);


  $new_user = User::create([
    'name' => $new_name,
    'pass' => $password,
    'mail' => $email,
    'status' => 1,
  ]);

  $new_user->set('field_first_name', $first_name);
  $new_user->set('field_last_name', $last_name);

  $new_user->save();



  _user_mail_notify('register_no_approval_required', $new_user);

  if ($new_user) {
    $uid = \Drupal::service('user.auth')->authenticate($new_user->getAccountName(), $password);
    if ($uid) {
      user_login_finalize($new_user);
      return ['account_created' => TRUE, 'uid' => $uid];
    }
  }

  return ['account_created' => $new_name];
}

