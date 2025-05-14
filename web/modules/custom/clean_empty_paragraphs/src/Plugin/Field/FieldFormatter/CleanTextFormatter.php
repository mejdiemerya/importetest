<?php

namespace Drupal\clean_empty_paragraphs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Render\Markup;

/**
 * Plugin implementation of the 'clean_empty_paragraphs' formatter.
 *
 * @FieldFormatter(
 *   id = "clean_empty_paragraphs",
 *   label = @Translation("Text (clean empty paragraphs)"),
 *   field_types = {
 *     "text_long",
 *     "text_with_summary"
 *   }
 * )
 */
class CleanTextFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $text = $item->value;
      // Supprimer <p> avec uniquement &nbsp;, espaces, ou UTF-8 nbsp
      $text = preg_replace('/<p>(\s|&nbsp;| )*<\/p>/i', '', $text);
      $elements[$delta] = ['#markup' => Markup::create($text)];
    }

    return $elements;
  }

}
