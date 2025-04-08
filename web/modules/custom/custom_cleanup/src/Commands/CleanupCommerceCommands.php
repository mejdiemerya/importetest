<?php

namespace Drupal\custom_cleanup\Drush\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Attributes as CLI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Supprime toutes les entités Commerce ciblées.
 */
class CleanupCommerceCommands extends DrushCommands {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct();
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  #[CLI\Command(name: 'custom_cleanup:cleanup_commerce', description: 'Supprime tous les order items, orders, licenses et subscriptions.')]
  public function cleanup(): void {
    $types = [
      'commerce_order_item' => 'Order items',
      'commerce_order' => 'Orders',
      'commerce_license' => 'Licenses',
      'commerce_subscription' => 'Subscriptions',
    ];

    foreach ($types as $entity_type => $label) {
      try {
        $storage = $this->entityTypeManager->getStorage($entity_type);
        $ids = $storage->getQuery()->accessCheck(FALSE)->execute();

        if (!empty($ids)) {
          $entities = $storage->loadMultiple($ids);
          $storage->delete($entities);
          $this->logger()->success("✅ $label supprimés : " . count($ids));
        }
        else {
          $this->logger()->info("ℹ️ Aucun $label trouvé.");
        }
      }
      catch (\Exception $e) {
        $this->logger()->error("❌ Erreur lors de la suppression de $label : " . $e->getMessage());
      }
    }

    $this->logger()->notice('🧹 Nettoyage terminé.');
  }
}
