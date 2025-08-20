<?php

namespace Drupal\event_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;

/**
 * This controller will return the all sites content json.
 */
class ContentApiController extends ControllerBase {

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
   * Constructs a new UpcomingAndPastEvents block for dependecy injection of entity type manager and file url generator.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileUrlGeneratorInterface $file_url_generator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_url_generator')
    );
  }

  /**
   * This will return the content data as a json format.
   */
  public function getAllContent() {
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple();

    $data = [];

    foreach ($nodes as $node) {
      $image_data = [
        'url' => NULL,
        'alt' => '',
        'title' => '',
      ];

      if ($node->hasField('field_event_banner_image') && !$node->get('field_event_banner_image')->isEmpty()) {
        $image_field = $node->get('field_event_banner_image')->first();
        $file = $image_field->entity;
        if ($file) {
          $absolute_url = $this->fileUrlGenerator->generateString($file->getFileUri());
          $image_data['url'] = $this->fileUrlGenerator->transformRelative($absolute_url, TRUE);
          $image_data['alt'] = $image_field->get('alt')->getString();
          $image_data['title'] = $image_field->get('title')->getString();
        }
      }

      $node_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => TRUE])->toString();

      $data[] = [
        'title' => $node->label(),
        'nid' => $node->id(),
        'image' => $image_data,
        'node_url' => $node_url,
      ];
    }

    return new JsonResponse($data);
  }

}
