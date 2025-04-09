<?php

namespace Drupal\bg_import_orders\Plugin\migrate\source;

use CommerceGuys\Intl\Currency\CurrencyRepository;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_store\Resolver\DefaultStoreResolver;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets Commerce   bg_commerce_order data from database.
 *
 * @MigrateSource(
 *   id = "bg_commerce_order",
 *   source_module = "commerce_order"
 * )
 */
class BgCommerceOrder extends FieldableEntity {

  /**
   * The default store resolver.
   *
   * @var \Drupal\commerce_store\Resolver\DefaultStoreResolver
   */
  protected $defaultStoreResolver;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, DefaultStoreResolver $default_store_resolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    $this->defaultStoreResolver = $default_store_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('state'),
      $container->get('entity_type.manager'),
      $container->get('commerce_store.default_store_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'order_id' => $this->t('Order ID'),
      'commerce_order_total' => $this->t('Order Number'),
      'revision_id' => $this->t('Revision ID'),
      'type' => $this->t('Type'),
      'uid' => $this->t('User ID'),
      'mail' => $this->t('Email'),
      'status' => $this->t('Status'),
      'created' => $this->t('Created'),
      'changed' => $this->t('Changed'),
      'default_store_id' => $this->t('Default store id'),
      'refresh_state' => $this->t('Order refresh state'),
      'hostname' => $this->t('Hostname'),
      'data' => $this->t('Data'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['order_id']['type'] = 'integer';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {

    $tabProducts = [
      'BGPRM-MI',
      'LUPRM-MI',
      'LUPRM-YI',
      'BGPNG-WELL-AP-Dv2',
      'LUPNG-V4AP-SITE',
      'LUPNG-V4AP',
      'BGPNG-WELL-APv2',
      'LUPNG-V4GA',
      'LUPRM-YT',
      'LUPRM-YT-20',
      'LUPRM-YT-30',
    ];
    $query = $this->select('commerce_order', 'ord')
      ->fields('ord');
      // $query->condition('ord.uid', 57813);
      // $query->condition('ord.order_id', 38844);
       $query->condition('ord.status', ['recurring_open','invoiced'],'IN');
    $query->addJoin('left','field_data_cl_billing_cycle', 'f1', 'f1.entity_id = ord.order_id');
    $query ->fields('f1', ["cl_billing_cycle_target_id"] );
    $query->addJoin('left','cl_billing_cycle', 'f2', 'f2.billing_cycle_id = f1.cl_billing_cycle_target_id');
    $query ->fields('f2', ["start","end"] );
    $query ->addField('f2',  "type","billing_schedule_sql"  );
    $query->addJoin('left','field_data_commerce_customer_billing', 'f3', 'f3.entity_id = ord.order_id');
    $query ->fields('f3', ["commerce_customer_billing_profile_id" ] );
    $query->addJoin('left','commerce_payment_transaction', 'f4', 'f4.order_id = ord.order_id');
    $query ->fields('f4', ["transaction_id" ] );
    $query->addJoin('inner','commerce_line_item', 'f5', 'f5.order_id = ord.order_id');
    $query ->fields('f5', ["line_item_label" ] );
    $query->condition('f5.line_item_label', $tabProducts, 'IN');
    $query->condition('ord.order_id', 208002 );//test a enlever
//    $query->addJoin('left','commerce_cardonfile', 'f3', 'f3.order_id = ord.order_id');
//    $query->addJoin('left','field_data_commerce_cardonfile_profile', 'f4', 'f4.entity_id = f3.card_id');
//    $query ->fields('f4', ["commerce_cardonfile_profile_profile_id" ] );

////    $query->addJoin('left','field_data_commerce_customer_billing', 'f1', 'f1.entity_id = ord.order_id');
//    $query ->fields('f1', ["commerce_customer_billing_profile_id"] );
    return $query;
  }


  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Fail early if a store does not exist on the destination.
    // Add refresh skip value to the row.
    $row->setSourceProperty('refresh_state', OrderInterface::REFRESH_SKIP);
    $default_store = $this->defaultStoreResolver->resolve();
    if ($default_store) {
      $row->setSourceProperty('default_store_id', $default_store->id());
    }
    else {
      throw new MigrateException('You must have a store saved in order to import orders.');
    }

    // Get Field API field values.
    $order_id = $row->getSourceProperty('order_id');
    $revision_id = $row->getSourceProperty('revision_id');
    foreach (array_keys($this->getFields('commerce_order', $row->getSourceProperty('type'))) as $field) {
      $row->setSourceProperty($field, $this->getFieldValues('commerce_order', $field, $order_id, $revision_id));
    }

    // Include the number of currency fraction digits in commerce_order_total.
    $currencyRepository = new CurrencyRepository();
    $value = $row->getSourceProperty('commerce_order_total');
    $currency_code = $value[0]['currency_code'];
    $value[0]['fraction_digits'] = $currencyRepository->get($currency_code)->getFractionDigits();
    $row->setSourceProperty('commerce_order_total', $value);
    $row->setSourceProperty('data', unserialize($row->getSourceProperty('data')));

    // Get shipping line items for this order.
    $query = $this->select('commerce_line_item', 'cli')
      ->fields('cli')
      ->condition('cli.order_id', $order_id)
      ->condition('cli.type', 'shipping');

    $shipping_line_items = $query->execute()->fetchAll();
    foreach ($shipping_line_items as $key => $shipping_line_item) {
      // Get Field API field values.
      $line_item_id = $shipping_line_item['line_item_id'];
      foreach (array_keys($this->getFields('commerce_line_item', 'shipping')) as $field) {
        $shipping_line_items[$key][$field] = $this->getFieldValues('commerce_line_item', $field, $line_item_id, $line_item_id);
      }
      $shipping_line_items[$key]['data'] = unserialize($shipping_line_item['data']);
      $currency_code = $shipping_line_items[$key]['commerce_total'][0]['currency_code'];
      $shipping_line_items[$key]['commerce_total'][0]['fraction_digits'] = $currencyRepository->get($currency_code)->getFractionDigits();
    }
    $row->setSourceProperty('shipping_line_items', $shipping_line_items);
    if($row->getSourceProperty('status') == 'invoiced')
      $row->setSourceProperty('status',"completed");
    if($row->getSourceProperty('status') == 'recurring_open'){
      $row->setSourceProperty('status',"draft");
      $row->setSourceProperty('type',"recurring");
      $row->setSourceProperty('order_number',null);
    }else{
      $row->setSourceProperty('type',"default");
    }
    if($row->getSourceProperty('billing_schedule_sql') == 'monthly'){
      $row->setSourceProperty('billing_schedule',"billing_monthly");
    }

    if($row->getSourceProperty('billing_schedule_sql') == 'annual'){
      $row->setSourceProperty('billing_schedule',"billing_annual");
    }

    $row->setSourceProperty('total', [
      'currency_code' => $value[0]["currency_code"],
      'number' => $value[0]["amount"]/100,
    ]);
    $card = $this->getCard($row->getSourceProperty('uid'));

    if(empty($card)){
      $card = $this->getCard($row->getSourceProperty('uid'), 1);
    }

    $row->setSourceProperty('payment_method',
      $card
    );

//canceled

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValues($entity_type, $field, $entity_id, $revision_id = NULL, $language = NULL) {
    $values = parent::getFieldValues($entity_type, $field, $entity_id, $revision_id, $language);
    // Unserialize any data blob in these fields.
    foreach ($values as $key => &$value) {
      if (isset($value['data'])) {
        $values[$key]['data'] = unserialize($value['data']);
      }
    }
    return $values;
  }
  public function getCard($uid, $nostatus = 0){
    $query = $this->select("commerce_cardonfile", 'f')
      ->fields('f', ["card_id"]);
    $query ->condition("f.uid", $uid );
    if(empty($nostatus)){
      $query ->condition("f.status", 1 );
      $query ->condition("f.instance_default", 1 );
    }

    $query ->orderBy("f.card_id", 'DESC' );
    $query ->range(0, 1 );

    $rows =  $query->execute()->fetchCol();
    if(!empty($rows)){
      return $rows[0];
    }
    return null;
  }

}
