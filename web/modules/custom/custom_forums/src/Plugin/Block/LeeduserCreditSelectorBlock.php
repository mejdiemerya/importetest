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
 * Provides a 'LeeduserCreditSelectorBlock' Block.
 *
 * @Block(
 *   id = "leeduser_credit_selector_form_block",
 *   admin_label = @Translation("Leeduser Credit Selector Form Block"),
 * )
 */
class LeeduserCreditSelectorBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\custom_forums\Form\CreditSelectorForm');
  }

}
