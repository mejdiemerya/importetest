<?php
namespace Drupal\bg_user_api\Plugin\rest\resource;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\user\Entity\User;
use Drupal\user\Plugin\rest\resource\UserRegistrationResource;
use Drupal\rest\Annotation\RestResource;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[\Drupal\rest\Attribute\RestResource(
  id: "bg_user_registration",
  label: new TranslatableMarkup("BG User registration"),
  serialization_class: User::class,
  uri_paths: [
    "create" => "/api/user/register",
  ],
)]
class BgUserRegistrationResource extends UserRegistrationResource {

  /**
   * Responds to user registration POST request.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account entity.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  public function post(?UserInterface $account = NULL) {

    $this->ensureAccountCanRegister($account);

    // Only activate new users if visitors are allowed to register.
    if ($this->userSettings->get('register') == UserInterface::REGISTER_VISITORS) {
      $account->activate();
    }
    else {
      $account->block();
    }

    // Generate password if email verification required.
    if ($this->userSettings->get('verify_mail')) {
      $account->setPassword($this->passwordGenerator->generate());
    }

    $this->checkEditFieldAccess($account);

    // Make sure that the user entity is valid (email and name are valid).
    $this->validate($account);

    // Create the account.
    $account->save();

    $this->sendEmailNotifications($account);

    return new ModifiedResourceResponse($account, 200);
  }

  /**
   * Ensure the account can be registered in this request.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account to register.
   */
  protected function ensureAccountCanRegister(?UserInterface $account = NULL) {
    if ($account === NULL) {
      throw new BadRequestHttpException('No user account data for registration received.');
    }

    // POSTed user accounts must not have an ID set, because we always want to
    // create new entities here.
    if (!$account->isNew()) {
      throw new BadRequestHttpException('An ID has been set and only new user accounts can be registered.');
    }

    // Only allow anonymous users to register, authenticated users with the
    // necessary permissions can POST a new user to the "user" REST resource.
    // @see \Drupal\rest\Plugin\rest\resource\EntityResource

//    if (!$this->currentUser->isAnonymous()) {
//      throw new AccessDeniedHttpException('Only anonymous users can register a user.');
//    }

    // Verify that the current user can register a user account.
    if ($this->userSettings->get('register') == UserInterface::REGISTER_ADMINISTRATORS_ONLY) {
      throw new AccessDeniedHttpException('You cannot register a new user account.');
    }

    if (!$this->userSettings->get('verify_mail')) {
      if (empty($account->getPassword())) {
        // If no email verification then the user must provide a password.
        throw new UnprocessableEntityHttpException('No password provided.');
      }
    }
    else {
      if (!empty($account->getPassword())) {
        // If email verification required then a password cannot provided.
        // The password will be set when the user logs in.
        throw new UnprocessableEntityHttpException('A Password cannot be specified. It will be generated on login.');
      }
    }
  }
}
