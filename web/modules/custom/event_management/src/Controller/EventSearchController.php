<?php

namespace Drupal\event_management\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 *
 */
class EventSearchController extends ControllerBase {

  /**
   *
   */
  public function searchAjax(Request $request) {
    $search = $request->query->get('search', '');

    // Fetch events (replace with your entity query or DB query).
    $query = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'event')
      ->condition('status', 1);

    if (!empty($search)) {
      $query->condition('title', '%' . $search . '%', 'LIKE');
    }
    else {
      $html = '';
      $html .= '<div class=""event-item>';
      $html .= '</div>';

      return new JsonResponse(['html' => $html]);
    }

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);

    // Render events as HTML string.
    $html = '';
    foreach ($nodes as $node) {
      $node_url = $node->toUrl()->toString();
      $html .= '<div class="event-item">';
      $html .= '<h3><a href="' . $node_url . '">' . $node->label() . '</a></h3>';
      $date_value = $node->get('field_event_date')->value;
      if (!empty($date_value)) {
        $date = new DrupalDateTime($date_value);
        $html .= '<p>' . $date->format('d M Y h:i A') . '</p>';
      }
      $html .= '</div>';
    }

    return new JsonResponse(['html' => $html]);
  }

}
