<?php

namespace Drupal\event_management\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'social_links_widget' widget.
 *
 * @FieldWidget(
 *   id = "social_links_widget",
 *   label = @Translation("Social Links Widget"),
 *   field_types = {
 *     "social_links"
 *   }
 * )
 */
class SocialLinksWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['facebook'] = [
      '#type' => 'url',
      '#title' => $this->t('Facebook URL'),
      '#default_value' => $items[$delta]->facebook ?? '',
      '#placeholder' => 'https://facebook.com/username',
    ];

    $element['twitter'] = [
      '#type' => 'url',
      '#title' => $this->t('Twitter URL'),
      '#default_value' => $items[$delta]->twitter ?? '',
      '#placeholder' => 'https://twitter.com/username',
    ];

    $element['linkedin'] = [
      '#type' => 'url',
      '#title' => $this->t('LinkedIn URL'),
      '#default_value' => $items[$delta]->linkedin ?? '',
      '#placeholder' => 'https://linkedin.com/in/username',
    ];

    return $element;
  }

}
