<?php

namespace Drupal\event_management\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * This class will collect the api authentication details.
 */
class ApiAuthConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['event_management.api_auth'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_management_api_auth_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('event_management.api_auth');

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Username'),
      '#default_value' => $config->get('username'),
      '#required' => TRUE,
    ];

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('API Password'),
      '#default_value' => $config->get('password'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('event_management.api_auth')
      ->set('username', $form_state->getValue('username'))
      ->set('password', $form_state->getValue('password'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
