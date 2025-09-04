<?php

namespace Drupal\event_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 *
 */
class BatchExampleController extends ControllerBase {

  /**
   *
   */
  public function startBatch() {
    // Step 1: Load all unpublished articles.
    $nids = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'article')
      ->condition('status', 0)
      ->execute();

    // Step 2: Define batch operations.
    $operations = [];
    foreach ($nids as $nid) {
      $operations[] = [
      // Callback.
        [self::class, 'publishArticle'],
      // Parameters.
        [$nid],
      ];
    }

    // Step 3: Batch definition.
    $batch = [
      'title' => $this->t('Publishing articles...'),
      'operations' => $operations,
      'finished' => [self::class, 'batchFinished'],
      'init_message' => $this->t('Batch is starting...'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('Batch has encountered an error.'),
    ];

    // Step 4: Start batch.
    batch_set($batch);
    // Redirect after finish.
    return batch_process('/');
  }

  /**
   * Operation callback.
   */
  public static function publishArticle($nid, &$context) {
    $node = Node::load($nid);
    if ($node) {
      $node->setPublished(TRUE)->save();
      $context['message'] = t('Published article: @title', ['@title' => $node->label()]);
    }
  }

  /**
   * Finished callback.
   */
  public static function batchFinished($success, $results, $operations) {
    if ($success) {
      \Drupal::messenger()->addMessage(t('All articles have been published.'));
    }
    else {
      \Drupal::messenger()->addMessage(t('Some operations failed.'), 'error');
    }
  }

}
