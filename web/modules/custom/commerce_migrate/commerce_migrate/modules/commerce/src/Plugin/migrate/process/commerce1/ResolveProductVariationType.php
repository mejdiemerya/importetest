<?php

namespace Drupal\commerce_migrate_commerce\Plugin\migrate\process\commerce1;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Resolve the product variation type.
 *
 * This plugin determines the product variation type referenced by a product
 * type. This is necessary because in 2.x, products can only be mapped to a
 * single product variation type, whereas in 1.x one product display node can
 * be mapped to multiple product types.
 *
 * The product variation type will be set to the same name as the product type,
 * if that name exists in the Commerce 1 product reference field. If it does
 * not exist then the name is set to 'default'. This solution may not work for
 * all sites. In that case, this plugin can be overwritten by custom migration
 * classes to provide the different logic for determining the target variation
 * type. Another option is to modify the migration yml file to use the
 * static_map process plugin instead.
 *
 * Available configuration keys:
 * - matching: (optional) Only used if there are more than one referenced
 *   product types. If set, returns the referenced type that matches the input
 *   type.
 * - default: (optional) Only used if there are more than one referenced
 *   product types. A default type to use when a matching type is not found.
 *
 * Example:
 *
 * @code
 * process:
 *   type:
 *     plugin: commerce1_resolve_product_variation_type
 *     source: type
 *     variations:
 *       matching: true
 *       default: trees
 * @endcode
 *
 * If the source value is 'boats' and there is a referenced type of 'boats' then
 * the return vale is 'boats'. If that source value is not 'boats' then 'trees'
 * is returned.
 *
 * @MigrateProcessPlugin(
 *   id = "commerce1_resolve_product_variation_type"
 * )
 */
class ResolveProductVariationType extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_string($value)) {
      throw new MigrateException(sprintf("Input should be an string, instead it was of type '%s'", gettype($value)));
    }

    $new_value = $value;

    // Get all the product types for this commerce display type.
    $product_variation_types = array_filter($row->getSourceProperty('data/settings/referenceable_types'));

    $count = count($product_variation_types);
    if ($count > 1) {
      // Assume the default type if it is set in the configuration.
      if (!empty($this->configuration['variations']['default'])) {
        $new_value = $this->configuration['variations']['default'];
      }
      // Try to find a variation type that matches the product type.
      if (isset($this->configuration['variations']['matching'])) {
        $key = array_search($value, $product_variation_types);
        if ($key !== FALSE) {
          $new_value = $product_variation_types[$key];
        }
      }
    }

    if ($count === 1) {
      $new_value = reset($product_variation_types);
    }

    return $new_value;
  }

}
