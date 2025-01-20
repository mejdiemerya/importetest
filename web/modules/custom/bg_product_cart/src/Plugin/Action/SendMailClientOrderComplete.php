<?php

namespace Drupal\bg_product_cart\Plugin\Action;

use Drupal;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Describes
 *
 *
 *
 * @Action(
 *   id = "send_mail_client_order_complete",
 *   label = @Translation("Send Mail to client after order complete"),
 *   type = "commerce_payment"
 * )
 */
class SendMailClientOrderComplete extends ActionBase {


  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = true) {
    $result = AccessResult::allowed();
     return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(  $entity = NULL,$product = NULL)
  {
    /** @var \Drupal\commerce_payment\Entity\Payment $entity */
$orders = $entity->getOrder()->getItems();
//dd($entity->getOrder()->getItems()[0]->getPurchasedEntity());
foreach ($orders as $order) {
  $variation = $order->getPurchasedEntity();
  //dd($variation->get('type')->target_id);
  if($variation->get('type')->target_id == 'membership' && $entity->get('avs_response_code')->value == 'Y'){
  // dd('hello');
    $send = $this->sendMail($variation);
   // dd($entity->get('avs_response_code')->value);
  }
  //dd($variation);
}//dd($entity->get('avs_response_code')->value);
   // dd($entity->getOrder()->get('order_items'));
    if($entity->get('avs_response_code')->value == 'Y'){
      $order_id = $entity->get('order_id')->value;

    }
    //dd($entity);
//    if(!empty($entity ))
// dd($entity->get("state")->value );
    //unauthorized_review
//    /** @var \Drupal\commerce_product\Entity\ProductInterface $entity */
//    dd($entity);
//    $mailManager = Drupal::service('plugin.manager.mail');
//
//    $module = 'bg_product_cart';
//    $key = 'notification_client_order';
//    $params['from'] = \Drupal::config('system.site')->get('mail');
//    $reply =  "admin@example.com";
//    $params['message'] = "rrrrrrrrrrrrr";
//    $params['subject'] = "ssssssssssssss";
//    $to =  "devdevdev@yopmail.com";
//    $langcode = Drupal::currentUser()->getPreferredLangcode();
//    $send = true;
//    $mailManager->mail($module, $key, $to, $langcode, $params, $reply, $send);
  }


private function sendMail($variation)
{
  $mailManager = Drupal::service('plugin.manager.mail');

    $module = 'bg_product_cart';
    $key = 'notification_client_order';
    $params['from'] = \Drupal::config('system.site')->get('mail');
    $reply =  "admin@example.com";
    $params['message'] = $variation->get('field_welcome_email_body')->value;
    $params['subject'] = $variation->get('field_welcome_email_subject')->value;

    $to =  Drupal::currentUser()->getEmail();
    $langcode = Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    return $mailManager->mail($module, $key, $to, $langcode, $params, $reply, $send);
}
}

