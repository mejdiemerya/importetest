<?php

namespace Drupal\bg_product_cart\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Url;

/**
 * Controller for the checkout complete page.
 */
class CheckoutCompleteController extends ControllerBase {

  /**
   * Builds the checkout complete page.
   */
  public function completePage($order_id) {

    // Load the order based on the order ID.
    $order = Order::load($order_id);

    if (!$order) {
      // Handle case where order doesn't exist.
      $message = $this->t('Order not found.');
    }else{
      if(!empty($order->getItems()[0]->getPurchasedEntity()->get('field_checkout_complete_message')->value)){
        $message = $order->getItems()[0]->getPurchasedEntity()->get('field_checkout_complete_message')->value;

        $message = str_replace("[commerce-order:order-number]", $order_id, $message);
        }
        else{
          $message = $this->t('Complete order.');
        }

    }



    // Render custom data for the checkout complete page.
    return [
      '#theme' => 'checkout_complete_page',
      //'#order' => $order,
      '#custom_message' => (string)($message),
    ];
  }


}
