<?php

namespace Drupal\event_management\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'social_links_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "social_links_formatter",
 *   label = @Translation("Social Links Icons"),
 *   field_types = {
 *     "social_links"
 *   }
 * )
 */
class SocialLinksFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $output = '<div class="social-links">';
      if (!empty($item->facebook)) {
        $output .= '<a href="' . $item->facebook . '" target="_blank">📘 Facebook</a> ';
      }
      if (!empty($item->twitter)) {
        $output .= '<a href="' . $item->twitter . '" target="_blank">🐦 Twitter</a> ';
      }
      if (!empty($item->linkedin)) {
        $output .= '<a href="' . $item->linkedin . '" target="_blank">💼 LinkedIn</a>';
      }
      $output .= '</div>';

      $elements[$delta] = [
        '#markup' => $output,
        '#allowed_tags' => ['a', 'div'],
      ];
    }

    return $elements;
  }

}
