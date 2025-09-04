<?php

namespace Drupal\event_management\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes event reminder emails.
 *
 * @QueueWorker(
 *   id = "event_reminder_queue",
 *   title = @Translation("Event Reminder Email Sender"),
 *   cron = {"time" = 60}
 * )
 */
class EventReminderQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  protected $mailManager;
  protected $logger;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MailManagerInterface $mail_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailManager = $mail_manager;
    $this->logger = $logger_factory->get('event_reminder');
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.mail'),
      $container->get('logger.factory')
    );
  }

  public function processItem($data) {
    // Send the email.
    $this->logger->notice('Processing queue item for: @mail', ['@mail' => $data['email']]);

    $params['subject'] = "Reminder: Your event is tomorrow!";
    $params['message'] = "Hello, this is a reminder for the event: " . $data['event_title'];

    $result = $this->mailManager->mail(
      'event_management',
      'event_reminder',
      $data['email'],
      'en',
      $params
    );

    if ($result['result']) {
      $this->logger->info('Reminder sent to %email for event %event.', [
        '%email' => $data['email'],
        '%event' => $data['event_title'],
      ]);
    } else {
      $this->logger->error('Failed to send reminder to %email.', [
        '%email' => $data['email'],
      ]);
    }
  }
}
