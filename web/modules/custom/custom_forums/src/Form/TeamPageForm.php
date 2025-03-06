<?php

namespace Drupal\custom_forums\Form;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_store\Entity\Store;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;


/**
 * Provides a TeamPageForm .
 */


class TeamPageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'team_page_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

      $account = \Drupal::currentUser();
    if($account->isAnonymous()) {
      $form['team_signup'] = [
        '#type' => 'markup',
        '#markup' => '
        <div id="team-signup" class="panel panel-neutral my-4 card">
          <div class="panel-heading card-header text-center">
            <h3 class="panel-title text-center m-0">TEAM MEMBERSHIP PRICING &amp; SIGNUP</h3>
          </div>
          <div class="panel-body card-body">
            <h4 class="mt0">Pricing:</h4>
            <p><strong>10-person team:</strong> $385/year<br>
              <strong>20-person team:</strong> $770/year<br>
              <strong>30-person team:</strong> $1,155/year</p>
            <p><a class="btn btn-warning btn-lg" href="/signup">Sign up now&nbsp;</a></p>
          </div>
        </div>',
        '#allowed_tags' => ['div', 'h3', 'h4', 'p', 'strong', 'br', 'a'], // Sécurité pour éviter XSS
      ];

    }else{

      $form['#attributes']['class'] = ['teams-form'];
      $team_products = ['LUPRM-YT', 'LUPRM-YT-20', 'LUPRM-YT-30'];
      $products =[];
      foreach ($team_products as $sku)
      {
        $product = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->loadBySku($sku);
        $products[] = array(
          'sku' => $sku,
          'variation_id' => $product->variation_id->value,
          'product_id' => $product->product_id->target_id,
          'name' => _bg_content_get_human_readable_product_name($product),
          'price' =>$product->price->number,
        );

      }
      $account=$this->currentUser()->id();
      $existing_groups = bg_content_get_groups_by_user($account, $type = 'team_account', $status = 0);
      if (!empty($existing_groups))
      {
        $existing_group_options['choose'] = 'Choose One';
        foreach ($existing_groups as $existing_group)
        {
          $existing_group_options[$existing_group->nid->value] = 'Renew: ' . $existing_group->title->value;
        }
        $existing_group_options['create'] = 'Start a new team';

        $form['existing_group'] = array(
          '#type' => 'select',
          '#title' => 'Select an option',
          '#options' => $existing_group_options,
          '#default_value' => 'choose',
        );
      }
      $form['group_name'] = [
        '#type' => 'textfield',
        '#title' => 'Name of team (Usually your company name)',
        '#required' => !empty($existing_groups) ? FALSE : TRUE,
        '#states' => [
          'visible' => [
            ':input[name="existing_group"]' => ['value' => 'create'],
          ],
        ],
      ];

      $product_options = array();
      $product_options['choose'] = 'Choose one';
      foreach ($products as $product)
      {
        $price =  $product['price'] + 0;
        if (strpos($product['price'], '.') !== FALSE)
        {
          $price = number_format($price, 2, '.', ',');

        }else{
          $price = number_format($price, 0, '.', ',');
        }

        $product_label = $product['name'] . ' - $' . $price;
        $product_options[$product['variation_id']] = $product_label;
      }
      $form['product'] = [
        '#type' => 'select',
        '#title' => 'Product',
        '#options' => $product_options,
        '#default_value' => 'choose',
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => 'Proceed to checkout »',
        '#attributes' => ['class' => ['btn-warning', 'btn-lg']],
      ];

    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $product = ProductVariation::load($values['product']);
    if (!empty($values['existing_group'])) {
      $group_node = Node::load($values['existing_group']);}
    $user = \Drupal::currentUser();

    if (!empty($values['existing_group'])) {
      $group_node = Node::load($values['existing_group']);
    } else {
      $team_products = [
        12 => 10,
        13 => 20,
        14 => 30,
      ];

      $product_code = (int) $values['product'];
      $field_nbr_max_value = isset($team_products[$product_code]) ? $team_products[$product_code] : 0;

      $group_node = Node::create([
        'type' => 'team_account',
        'title' => $values['group_name'],
        'uid' => $user->id(),
        'field_nbr_max' => $field_nbr_max_value,
        'status' => 0,
      ]);
      $group_node->save();

    }

    $store = Store::load(1);
    $cart_manager = \Drupal::service('commerce_cart.cart_manager');
    $cart_provider = \Drupal::service('commerce_cart.cart_provider');
    $cart = $cart_provider->getCart('default', $store, $user);
    if ($cart) {
      $cart_manager->emptyCart($cart);
    }

    $line_item = commerce_node_checkout_add_node($group_node, $product, $user);

    $order = Order::load($line_item->get('order_id')->target_id);

    $form_state->setRedirect('commerce_checkout.form', ['commerce_order' => $order->id()]);
  }


}


/**
 * Adds a product to a node-based checkout process.
 */
function commerce_node_checkout_add_node($node, $product, $account = NULL) {
  if (!$account) {
    $account = \Drupal::currentUser();
  }

  if ($account instanceof User) {
    $uid = $account->id();
  } else {
    $uid = \Drupal::currentUser()->id();
  }

  $store = Store::load(1);
  $cart_provider = \Drupal::service('commerce_cart.cart_provider');
  $cart = $cart_provider->getCart('default', $store,  $account);
  $orderTypeResolver = \Drupal::service('commerce_order.chain_order_type_resolver');
  if (!$cart) {
    $cart = \Drupal::entityTypeManager()
      ->getStorage('commerce_order')
      ->create([
        'type' => 'default',
        'state' => 'draft',
        'uid' => $uid,
        'store_id' => $store,
        'cart' => TRUE,
      ]);
    $cart->save();
  }
  $cart_manager = \Drupal::service('commerce_cart.cart_manager');
  $variation = \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->load($product->id());

  $order_item_storage = \Drupal::entityTypeManager()->getStorage('commerce_order_item');

  $order_item = $order_item_storage->create([
    'type' => 'commerce_node_checkout',
    'purchased_entity' => $variation->id(),
    'title' => $variation->getOrderItemTitle() .'(' .$node->getTitle(). ')',
    'quantity' => 1,
    'field_associated_content'=>$node->id(),
    'unit_price' => $variation->getPrice(),
  ]);
  $order_item->save();
 \Drupal::logger('commerce')->debug('Product ID: @pid', ['@pid' => json_encode($order_item->id())]);

    $cart = $cart_provider->getCart('default', $store, $account);

    if (empty($cart)) {
      $cart = $cart_provider->createCart('default', $store, $account);
    }
  $order_item->set('order_id', $cart->id());
  $line_item = $cart_manager->addOrderItem($cart, $order_item,false);



  return $line_item;
}



function _bg_content_get_human_readable_product_name($product){

  if (!empty($product->field_bg_commerce_license_desc))
  {
    $name = $product->field_bg_commerce_license_desc->value;
  }else{
    $name = $product->title;
  }
  return $name;
}

function bg_content_get_groups_by_user($account, $type = FALSE, $status = 'all'){
  $groups = array();
  $assigned_groups = _get_groups_by_user($account);
  if ($assigned_groups)
  {
    foreach ($assigned_groups as $group_nid)
    {
      $group_node = \Drupal::entityTypeManager()->getStorage('node')->load($group_nid);

        $groups[] = $group_node;
    }
  }
  return $groups;
}
function _get_groups_by_user($account = NULL, $group_type = NULL) {
  if (!$account) {
    $account = \Drupal::currentUser();
  }

  if ($account instanceof User) {
    $uid = $account->id();
  } else {
    $uid = \Drupal::currentUser()->id();
  }
  $nids = \Drupal::entityQuery('node')
    ->condition('type', 'team_account')
    ->condition('uid', $uid)->accessCheck(FALSE)
    ->execute();

  if (!empty($nids)) {
    return $nids;
  }
  return [];
}
