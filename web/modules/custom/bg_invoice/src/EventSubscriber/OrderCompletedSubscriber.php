<?php

namespace Drupal\bg_invoice\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Drupal\commerce_order\Entity\OrderInterface;

class OrderCompletedSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [
      // Cet événement est déclenché après une transition d'état.
      'commerce_order.place.post_transition' => 'onOrderCompleted',
    ];
  }

  public function onOrderCompleted(WorkflowTransitionEvent $event) {
    $order = $event->getEntity();

    if ($order instanceof OrderInterface && $order->getState()->getId() == 'completed') {
      // Appelle ta fonction d'envoi d'email.
      \Drupal::logger('bg_invoice')->notice('Commande complétée : ' . $order->id());
      order_email_send_notification($order);
    }
  }

}
