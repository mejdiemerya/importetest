<?php


namespace Drupal\bg_custom_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the custom checkout pane.
 *
 * @CommerceCheckoutPane(
 *   id = "block_progres_review",
 *   label = @Translation("block_progres_review"),
 * )
 */
class Progresreview extends CheckoutPaneBase
{

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * Constructs a new CustomCheckoutPane object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Block\BlockManager $block_manager
   *   The block manager.
   * @param \Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface|null $checkout_flow
   *   The checkout flow, or NULL.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BlockManager $block_manager, ?CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, ?CheckoutFlowInterface $checkout_flow = null)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.block'),
      $checkout_flow,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary()
  {
    return [
      '#markup' => $this->t('Displays a custom block.'),
    ];
  }

  /**
   * {@inheritdoc}
   */


  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form)
  {
    // Load the block plugin.
    $block_plugin = $this->blockManager->createInstance('commerce_checkout_progress', []);
    $pane_form['block'] = $block_plugin->build();

    return $pane_form;
  }
}
