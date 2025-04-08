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
class SendMailClientOrderComplete extends ActionBase
{


  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = true)
  {
    $result = AccessResult::allowed();
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL, $product = NULL)
  {
    $order = \Drupal::routeMatch()->getParameter('commerce_order');

    if ($order instanceof Drupal\commerce_order\Entity\Order) {
      $orderItems = $order->getItems();

      foreach ($orderItems as $orderItem) {
        /** @var \Drupal\commerce_order\Entity\OrderItem $orderItem */
        $variation = $orderItem->getPurchasedEntity();

        if ($variation && $variation->get('type')->target_id == 'membership' ) {
          $send = $this->sendMail($variation);
        }
      }
    }


  }


  /**
   * @param $variation
   * @return mixed
   */
  private function sendMail($variation)
  {
    $mailManager = Drupal::service('plugin.manager.mail');

    $module = 'bg_product_cart';
    $key = 'notification_client_order';
    $params['from'] = \Drupal::config('system.site')->get('mail');
    $reply = "admin@example.com";
    $params['message'] = $variation->get('field_welcome_email_body')->value;
    $params['subject'] = $variation->get('field_welcome_email_subject')->value;

    $to = Drupal::currentUser()->getEmail();
    $langcode = Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    return $mailManager->mail($module, $key, $to, $langcode, $params, $reply, $send);
  }
}

