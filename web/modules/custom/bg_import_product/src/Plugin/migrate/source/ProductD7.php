<?php
namespace Drupal\buildgreen_migrate\Plugin\migrate\source;


use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\FileTransfer\FileTransfer;
use Drupal\file\Entity\File;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
/**
 * Extract nodes from Drupal 7 database.
 *
 * @MigrateSource(
 * id = "ProductD7",
 * source_module = "migrate_plus"
 * )
 */
class ProductD7 extends SqlBase {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $fields = [
      'nid',
      'title',
      'type',
    ];
    return $this->select('node', 'n')
      ->condition('type', array('e_membership_display', 'e_product_display', 'license_notification'), 'IN')
      ->fields('n', $fields);

  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'nid' => $this->t('Product ID'),
      'title' => $this->t('Name of product'),
      'type' => $this->t('type of product'),
      'variations' => $this->t('variations of product'),
      //'body' => $this->t('Full description    '),

    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'b',
      ],
    ];
  }

  public function getProductVariation( $nid) {
    $result = $this->select("field_data_field_product", 'f')
      ->fields('f', ["field_product_product_id"])
      ->condition('bundle', array('e_membership_display', 'e_product_display', 'license_notification'), 'IN')
      ->condition('f.entity_id', $nid, '=');
    $row =  $result->execute()->fetchObject();
    if(!empty($row->field_product_product_id)){
      $resultP = $this->select(" commerce_product", 'p');
      $resultP  ->fields('p', ["sku","title","data","product_id"]);
      $resultP->addJoin('inner','field_data_commerce_price', 'pt', 'p.product_id = pt.entity_id');
      $resultP->fields('pt', ["commerce_price_amount","commerce_price_currency_code"]);
      $resultP->condition('p.product_id', $row->field_product_product_id, '=');
      $rows =  $resultP->execute()->fetchAll();
      return $rows;
    }
    return false;

  }

  public function createVariation($data, $nid) {
    $storage = \Drupal::entityTypeManager()
      ->getStorage('commerce_product_variation');
    $new_variation =  $storage->create([
      'type' => 'default',
      'title' => "aaa",
      'uid' => 1,
      'sku' => $data['sku'],
      'price' => new Price($data['commerce_price_amount'], $data['commerce_price_currency_code']),
      'product_id' => $nid,//$data['product_id'],
    ]);

    $storage->save($new_variation);

    return $new_variation->id();
    /*$new_variation = ProductVariation::create([
      'type' => 'default',
      'title' => "aaa",
      'sku' => $data['sku'],
      'price' => new Price($data['commerce_price_amount'], $data['commerce_price_currency_code']),
      'product_id' => $nid,//$data['product_id'],
    ]);
    $new_variation->save();
    return $new_variation;*/
  }
  public function updateVariation($data,$nid) {
    $variation =  ProductVariation::load($data['product_id']);
    if($variation){
      $variation->set('title', $data['title']);
      $variation->set('product_id', $nid);
      $variation->save();
    }
    return $variation->id() ;
  }
  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row): bool {
     //var_dump($row);die;
    $nid = $row->getSourceProperty('nid');
    $variations = $this->getProductVariation( $nid);
    $resVariation = [];
    if(!empty($variations)){
      //var_dump($variations);
      foreach ($variations as $var) {
       // $resVariation  =['target_id' => $this->updateVariation($var,$nid)] ;


       // $tabVar[] =  $var['product_id'];
       //$resVariation[] = $this->createVariation($var,$nid);
       /*   if($resVariation == 1){
          // var_dump($var);
           $tabVar[] =  $var['sku'];

         }*/
      }
    }

   // $row->addVariation
    if(!empty($resVariation)){
     // var_dump(  ($resVariation));
      $row->setSourceProperty('variations',  $resVariation);
    }
    if(!empty($tabVar)){
     // var_dump($nid);
      //var_dump($tabVar);
      //$row->setSourceProperty('variations',  $tabVar);
    }
   // $row->setSourceProperty('variations[]',  array(8,10));

    //var_dump($nid.'--'.$x);
    // var_dump($row);
    // As explained above, we need to pull the style relationships into our
    // source row here, as an array of 'style' values (the unique ID for
    // the beer_term migration).
    /*$terms = $this->select('migrate_example_beer_topic_node', 'bt')
      ->fields('bt', ['style'])
      ->condition('bid', $row->getSourceProperty('bid'))
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('terms', $terms);

    // As we did for favorite beers in the user migration, we need to explode
    // the multi-value country names.
    if ($value = $row->getSourceProperty('countries')) {
      $row->setSourceProperty('countries', explode('|', $value));
    }*/
    //$row->setSourceProperty('type', "default");
    $row->rehash();
    return parent::prepareRow($row);
  }
}
