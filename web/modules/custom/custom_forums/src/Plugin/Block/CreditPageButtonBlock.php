<?php
namespace Drupal\custom_forums\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a 'CreditPageButtonBlock' Block.
 *
 * @Block(
 *   id = "leeduser_credit_page_button",
 *   admin_label = @Translation("Credit Page Button Block"),
 * )
 */
class CreditPageButtonBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get query parameters.
    $params = \Drupal::request()->query->all();
    $tipsheet_filter = FALSE;
    $output = '';
    $credit_id = NULL;

    // Check if the 'f' parameter contains multiple items.
    if (isset($params['f'])) {
      // Loop through each parameter to extract key-value pairs.
      foreach ($params['f'] as $param) {
        if (preg_match('/^tipsheet:(\d+)$/', $param, $matches)) {
          $tipsheet = $matches[1];
          $tipsheet_filter = TRUE;
          $tipsheet = Node::load($tipsheet);
        }
        elseif (preg_match('/^credit:(\d+)$/', $param, $matches)) {
          $credit_id = $matches[1];
        }
      }
    }

    // If no credit or tipsheet, return an empty render array.
    if (empty($credit_id) && !$tipsheet_filter) {
      return [];
    }

    // If it's a tipsheet filter, handle accordingly.
    if ($tipsheet_filter) {
      // Assuming $well_tipsheet_nids is accessible from the block.
      $well_tipsheet_name = \Drupal::config('custom_forums.settings')->get('well_tipsheet_name');
      $well_tipsheet_nids = $this->getWellTipsheet($well_tipsheet_name);

      if (isset($well_tipsheet_nids) && in_array($tipsheet->id(), $well_tipsheet_nids)) {
        foreach ($well_tipsheet_nids as $k => $well_tipsheet_nid) {

          $well_tipsheet = Node::load($well_tipsheet_nid);
          $url = Url::fromRoute('entity.node.canonical', ['node' => $well_tipsheet->id()])->toString();

          $output .= '<a class="w-100 mb-2 btn btn-lg btn-wrap wide btn-info text-start btn-icon" style="padding: 10px 15px;" href="' . $url . '">';
          $output .= '<i class="glyphicon glyphicon-leaf hidden-sm"></i> <div>Read <strong><em>' . Html::escape($well_tipsheet->label()) . '</em></strong>&nbsp;&raquo;</div></a>';

          if ($k === 0) {
            $output .= '<br />';
          }
        }
      }
      else {
        $url = Url::fromRoute('entity.node.canonical', ['node' => $tipsheet->id()])->toString();
        $output .= '<a class="mb-2 w-100 btn btn-lg btn-wrap wide btn-info text-start btn-icon" style="padding: 10px 15px;" href="' . $url . '">';
        $output .= '<i class="glyphicon glyphicon-leaf hidden-sm"></i> <div>Read <strong><em>' . Html::escape($tipsheet->label()) . '</em></strong>&nbsp;&raquo;</div></a>';
      }
    }
    // Handle credit if no tipsheet filter.
    else {
      $credit = Term::load($credit_id);
      if ($credit instanceof Term) {
        $credit_label = Html::escape($this->getCreditFilterLabel($credit->id())['title']);
        $url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $credit->id()])->toString();
        $output .= '<a class="mb10 btn btn-wrap wide btn-info text-left btn-icon" style="padding: 10px 15px;" href="' . $url . '">';
        $output .= '<i class="glyphicon glyphicon-leaf hidden-sm"></i> <div>See LEEDuser&rsquo;s guidance on ' . $credit_label . '&nbsp;&raquo;</div></a>';
      }
    }

    return [
      '#markup' => $output,
    ];
  }


  function getWellTipsheet($titles) {
    // Load the term storage handler.
    $term_storage = \Drupal::entityTypeManager()->getStorage('node');

    // Create an entity query for terms.
    $query = $term_storage->getQuery()
      ->condition('type', 'tipsheet')
      ->condition('title', explode(',', $titles), 'IN') // Handle multiple titles.
      ->accessCheck(false);

    // Execute the query and fetch the TID.
    $tids = $query->execute();

    // Return the first TID found, or NULL if none found.
    return $tids;
  }
  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // If you need to redefine the Max Age for that block
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['url.path', 'url.query_args'];
  }
  function loadAllParentsModal($tid) {

    \Drupal::logger('custom_module')->notice('Form state parents tid: @values', ['@values' => $tid]);

    $parents = [];
    $term = Term::load($tid);

    \Drupal::logger('custom_module')->notice('Form state parents tid2: @values', ['@values' =>  json_encode($term)]);

    if (  $term) {
      $parents[] = $term;
      $n = 0;

      while (isset($parents[$n]) && $parent_id = $parents[$n]->parent->target_id) {

        if ($parent = Term::load($parent_id)) {
          $parents[] = $parent;
          $n++;
        } else {
          break;
        }
      }
    }
    \Drupal::logger('custom_module')->notice('Form state parents tid2: @values', ['@values' =>  json_encode($parents)]);


    return $parents;
  }

  function getCreditFilterLabel($tid) {
    $parents = $this->loadAllParentsModal($tid);
    \Drupal::logger('custom_module')->notice('Form state parents: @values', ['@values' => $tid]);
    $count = count($parents);
    $data = array('title' => '', 'description' => '');
    switch ($count) {
      case 1:
        // Only one item means we are looking at LEED Version.
        $data['title'] = strip_tags($parents[0]->name->value);
        break;

      case 2:
        $data['title'] = strip_tags($parents[0]->name->value);
        break;

      case 3:
        $data['title'] = strip_tags(sprintf('%s %s', $parents[1]->name->value, $parents[0]->name->value));
        if (isset($parents[0]->description) && !empty($parents[0]->description->value)) {
          $data['description'] = strip_tags($parents[0]->description->value);
        }
        break;

      case 4:
        $data['title'] = strip_tags(sprintf('%s %s', $parents[2]->name->value, $parents[0]->name->value));
        if (isset($parents[0]->description) && !empty($parents[0]->description->value)) {
          $data['description'] = strip_tags($parents[0]->description->value);
        }
        break;
    }
    return $data;
  }

}
