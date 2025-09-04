<?php

namespace Drupal\event_management\Access;

use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Custom access check for API authentication.
 *
 * This class performs HTTP Basic Authentication for API routes.
 * It compares the credentials entered in the request with those
 * stored in the Drupal config form.
 */
class ApiAuthCheck {

  /**
   * The request stack service.
   *
   * Used to get the current HTTP request and read its headers.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The config factory service.
   *
   * Used to read stored username/password from config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service to access stored API credentials.
   */
  public function __construct(RequestStack $requestStack, ConfigFactoryInterface $config_factory) {
    $this->requestStack = $requestStack;
    $this->configFactory = $config_factory;
  }

  /**
   * Checks access for API routes.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns allowed() if credentials match, otherwise 401 Unauthorized.
   */
  public function access() {
    // Get the current HTTP request.
    $request = $this->requestStack->getCurrentRequest();

    // Load stored credentials from config (set via admin form).
    $config = $this->configFactory->get('event_management.api_auth');
    $stored_username = $config->get('username');
    $stored_password = $config->get('password');

    // Retrieve the username and password from HTTP Basic Auth headers.
    $username = $request->headers->get('php-auth-user');
    $password = $request->headers->get('php-auth-pw');

    // Compare request credentials with stored config credentials.
    if ($username === $stored_username && $password === $stored_password) {
      return AccessResult::allowed();
    }

    // If authentication fails Return HTTP 401 Unauthorized.
    $response = new Response('Unauthorized', 401, [
      'WWW-Authenticate' => 'Basic realm="Access to the API"',
    ]);
    $response->send();
    exit;
  }

}
