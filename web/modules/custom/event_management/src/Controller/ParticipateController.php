<?php

namespace Drupal\event_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;

/**
 * Handles AJAX participation.
 */
class ParticipateController extends ControllerBase {

  /**
   * POST /participate/ajax
   * Body: { nid: 123 }
   */
  public function participate(Request $request): JsonResponse {
    $account = $this->currentUser();
    if ($account->isAnonymous()) {
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t('Please log in to participate.'),
      ], 403);
    }

    $nid = (int) ($request->request->get('nid') ?? 0);
    if (!$nid) {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Missing nid.',
      ], 400);
    }

    /** @var \Drupal\node\NodeInterface|null $event */
    $event = Node::load($nid);
    if (!$event || $event->bundle() !== 'event') {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Invalid event.',
      ], 400);
    }

    $uid = (int) $account->id();

    // Check if this user already has a 'participate' node.
    $existing = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'participate')
      ->condition('field_user', $uid)
      ->range(0, 1)
      ->execute();

    if (!empty($existing)) {
      // Load the existing participation node.
      $participation_nid = reset($existing);
      /** @var \Drupal\node\Entity\Node $participation */
      $participation = Node::load($participation_nid);

      // Get current field_event values.
      $current_events = $participation->get('field_event')->getValue();
      $event_ids = array_column($current_events, 'target_id');

      // Check if the event is already in the list.
      if (in_array($nid, $event_ids)) {
        return new JsonResponse([
          'status' => 'already',
          'message' => $this->t('You already participated in @title.', [
            '@title' => $event->label(),
          ]),
        ]);
      }

      // Add the new event to the multi-value field.
      $current_events[] = ['target_id' => $nid];
      $participation->set('field_event', $current_events);
      $participation->save();

      return new JsonResponse([
        'status' => 'success',
        'message' => $this->t('Your participation has been updated for @title.', [
          '@title' => $event->label(),
        ]),
      ]);
    }

    // No participation node exists for this user — create one.
    $participation = Node::create([
      'type' => 'participate',
      'title' => 'Participation: ' . $account->getDisplayName(),
      'field_event' => [['target_id' => $nid]],
      'field_user' => $uid,
      'uid' => $uid,
      'status' => 1,
    ]);
    $participation->save();

    return new JsonResponse([
      'status' => 'success',
      'message' => $this->t('Participation saved for @title.', [
        '@title' => $event->label(),
      ]),
    ]);
  }

  /**
   * POST /participate/ajax
   * Body: { nid: 123 }
   */
  public function participateRemove(Request $request): JsonResponse {
    $account = $this->currentUser();
    if ($account->isAnonymous()) {
      return new JsonResponse([
        'status' => 'error',
        'message' => $this->t('Please log in to participate.'),
      ], 403);
    }

    $nid = (int) ($request->request->get('nid') ?? 0);
    if (!$nid) {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Missing nid.',
      ], 400);
    }

    /** @var \Drupal\node\NodeInterface|null $event */
    $event = Node::load($nid);
    if (!$event || $event->bundle() !== 'event') {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Invalid event.',
      ], 400);
    }

    $uid = (int) $account->id();

    // Check if this user already has a 'participate' node.
    $existing = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'participate')
      ->condition('field_user', $uid)
      ->range(0, 1)
      ->execute();

    if (!empty($existing)) {
      // Load the existing participation node.
      $participation_nid = reset($existing);
      /** @var \Drupal\node\Entity\Node $participation */
      $participation = Node::load($participation_nid);

      // Get current field_event values.
      $current_events = $participation->get('field_event')->getValue();
      $event_ids = array_column($current_events, 'target_id');

      // Check if the event is already in the list.
      if (in_array($nid, $event_ids)) {
        $field_event = $participation->get('field_event');

        foreach ($field_event as $delta => $item) {
          if ((int) $item->target_id === $nid) {
            $field_event->removeItem($delta);
            break;
          }
        }

        $participation->save();

        return new JsonResponse([
          'status' => 'success',
          'message' => $this->t('Your participation has been updated for @title.', [
            '@title' => $event->label(),
          ]),
        ]);
      }
      else {
        return new JsonResponse([
          'status' => 'error',
          'message' => 'You are not participating in this event.',
        ], 400);
      }
    }
    else {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Invalid event.',
      ], 400);
    }
  }

  /**
   *
   */
  public function checkParticipation($nid): JsonResponse {
    $uid = $this->currentUser()->id();
    $existing = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'participate')
      ->condition('field_user', $uid)
      ->range(0, 1)
      ->execute();

    if (!empty($existing)) {
      // Load the existing participation node.
      $participation_nid = reset($existing);
      /** @var \Drupal\node\Entity\Node $participation */
      $participation = Node::load($participation_nid);

      // Get current field_event values.
      $current_events = $participation->get('field_event')->getValue();
      $event_ids = array_column($current_events, 'target_id');

      // Check if the event is already in the list.
    }
    if (in_array($nid, $event_ids)) {
      return new JsonResponse(['status' => 'participated']);
    }
    else {
      return new JsonResponse(['status' => 'not_participated']);
    }

  }

  /**
   * This function will check if the event is already participated or not.
   */
  public function checkExistingEventParticipate() {

  }

}
