<?php

namespace Drupal\event_management\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'social_links' field type.
 *
 * @FieldType(
 *   id = "social_links",
 *   label = @Translation("Social Links"),
 *   description = @Translation("Stores Facebook, Twitter, and LinkedIn links."),
 *   default_widget = "social_links_widget",
 *   default_formatter = "social_links_formatter"
 * )
 */
class SocialLinksItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['facebook'] = DataDefinition::create('string')
      ->setLabel(t('Facebook URL'));

    $properties['twitter'] = DataDefinition::create('string')
      ->setLabel(t('Twitter URL'));

    $properties['linkedin'] = DataDefinition::create('string')
      ->setLabel(t('LinkedIn URL'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'facebook' => ['type' => 'varchar', 'length' => 255],
        'twitter' => ['type' => 'varchar', 'length' => 255],
        'linkedin' => ['type' => 'varchar', 'length' => 255],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $facebook = $this->get('facebook')->getValue();
    $twitter = $this->get('twitter')->getValue();
    $linkedin = $this->get('linkedin')->getValue();
    return empty($facebook) && empty($twitter) && empty($linkedin);
  }

}
