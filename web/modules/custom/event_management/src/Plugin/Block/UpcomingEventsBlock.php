<?php

namespace Drupal\event_management\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Provides an 'Upcoming Events' block.
 *
 * @Block(
 *   id = "upcoming_events_block",
 *   admin_label = @Translation("Upcoming Events")
 * )
 */
class UpcomingEventsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new UpcomingEventsBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_node = $this->routeMatch->getParameter('node');
    $current_nid = $current_node ? $current_node->id() : NULL;

    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'event')
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->condition('field_event_date', date('Y-m-d'), '>')
      ->sort('field_event_date', 'ASC');
    if ($current_nid) {
      $query->condition('nid', $current_nid, '<>');
    }

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $items = [];
    foreach ($nodes as $node) {
      $items[] = [
        '#markup' => $node->toLink()->toString(),
      ];
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#cache' => [
        'contexts' => ['route'],
        'tags' => ['node_list:event'],
      ],
    ];
  }

}
