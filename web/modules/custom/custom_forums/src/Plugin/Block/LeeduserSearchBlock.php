<?php
namespace Drupal\custom_forums\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a 'LeeduserSearchBlock' Block.
 *
 * @Block(
 *   id = "leeduser_search_block",
 *   admin_label = @Translation("Leeduser Search Block"),
 * )
 */
class LeeduserSearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\custom_forums\Form\LeeduserSearchForm');
  }

}
