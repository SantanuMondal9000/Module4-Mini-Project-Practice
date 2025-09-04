<?php

namespace Drupal\event_management\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a block with upcoming and past events.
 *
 * @Block(
 *   id = "upcoming_and_past_events",
 *   admin_label = @Translation("Upcoming and Past Events")
 * )
 */
class UpcomingAndPastEvents extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a new UpcomingAndPastEvents block.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $nids = $query->condition('type', 'event')
      ->condition('status', 1)
      ->sort('field_event_date', 'ASC')
      ->accessCheck(TRUE)
      ->execute();

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    $live_events = [];
    $past_events = [];
    $current_time = new DrupalDateTime('now');

    /** @var \Drupal\node\NodeInterface $node */
    foreach ($nodes as $node) {
      $event_date = new DrupalDateTime($node->get('field_event_date')->value);
      $banner_url = NULL;

      if ($node->hasField('field_event_banner_image') && !$node->get('field_event_banner_image')->isEmpty()) {
        $file = $node->get('field_event_banner_image')->entity;
        if ($file) {
          $absolute_url = $this->fileUrlGenerator->generateString($file->getFileUri());
          $banner_url = $this->fileUrlGenerator->transformRelative($absolute_url, TRUE);
        }
      }

      if ($event_date->format('Y-m-d') === $current_time->format('Y-m-d')) {
        // Live event.
        $live_events[] = [
          'title' => $node->label(),
          'date' => $event_date->format('d M Y'),
          'banner_url' => $banner_url,
          'url' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(),
          'is_live' => TRUE,

        ];
      }
      elseif ($event_date < $current_time) {
        // Past event.
        $past_events[] = [
          'title' => $node->label(),
          'date' => $event_date->format('d M Y'),
          'banner_url' => $banner_url,
          'url' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(),
          'is_live' => FALSE,
        ];
      }
      else {
        // Upcoming event.
        $live_events[] = [
          'title' => $node->label(),
          'date' => $event_date->format('d M Y'),
          'banner_url' => $banner_url,
          'url' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(),
          'is_live' => FALSE,
        ];
      }
    }

    return [
      '#theme' => 'event_cards',
      '#live_events' => $live_events,
      '#past_events' => $past_events,
      '#attached' => [
        'library' => [
          'event_management/events_block_styles',
        ],
      ],
      '#cache' => [
        'contexts' => ['route', 'url.path'],
        'tags' => ['node_list:event'],
      ],
    ];
  }

}
