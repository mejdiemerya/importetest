<?php

namespace Drupal\bg_product_cart\EventSubscriber;

use Drupal\commerce_order\Event\OrderEvents;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce_order\Event\OrderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderCompletedSubscriber.
 */
class OrderCompletedSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new OrderCompletedSubscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * React to order status updates.
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The order event.
   */
  public function onOrderUpdate(OrderEvent $event) {
    $order = $event->getOrder();

    // Vérifie si la commande est "completed".
    if ($order->getState()->getId() === 'completed') {
      // Récupère les order items.
      foreach ($order->getItems() as $order_item) {
        // Vérifie si l'article est de type "commerce_node_checkout".
        if ($order_item->bundle() === 'commerce_node_checkout') {
          // Récupère le nœud team_account lié à cette commande.
          $query = $this->entityTypeManager->getStorage('node')->getQuery()
            ->condition('type', 'team_account')
            ->condition('uid', $order->getCustomerId()) // Filtrer par utilisateur si applicable.
            ->condition('status', 0) // Non publié.
              ->accessCheck(true)
            ->range(0, 1);

          $nids = $query->execute();

          if (!empty($nids)) {
            $node = $this->entityTypeManager->getStorage('node')->load(reset($nids));
            $node->setPublished();
            $node->save();
          }
          break; // Arrêter la boucle une fois qu'on a trouvé un item correspondant.
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      OrderEvents::ORDER_UPDATE => 'onOrderUpdate',
    ];
  }

}
