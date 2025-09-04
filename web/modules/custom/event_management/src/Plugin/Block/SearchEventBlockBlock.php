<?php

declare(strict_types=1);

namespace Drupal\event_management\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a search event block block.
 *
 * @Block(
 *   id = "event_management_search_event_block",
 *   admin_label = @Translation("Search Event Block"),
 *   category = @Translation("Custom"),
 * )
 */
final class SearchEventBlockBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $events = [
      ['title' => 'Music Festival', 'date' => '2025-09-01'],
      ['title' => 'Art Exhibition', 'date' => '2025-09-15'],
    ];
    $searchTerm = 'music';
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return [
      '#theme' => 'event_search_results',
      '#events' => '',
      '#search_term' => '',
      '#attached' => [
        'library' => [
          'event_management/event_search',
        ],
      ],
    ];
  }

}
