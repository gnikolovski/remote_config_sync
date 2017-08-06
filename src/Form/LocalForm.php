<?php

namespace Drupal\remote_config_sync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LocalForm.
 */
class LocalForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'local_config_sync_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      'remote_config_sync.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('remote_config_sync.settings');

    if (!$config->get('token')) {
      $token = $this->guidv4();
      $config->set('token', $token)
        ->save();
    }

    $form['local'] = [
      '#type' => 'fieldset',
    ];

    $form['local']['notice'] = [
      '#type' => 'markup',
      '#markup' => $this->t('If you want to connect from a remote site and push the configuration from there, you will need this token.'),
    ];

    $form['local']['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#description' => $this->t('Security token for your local site.'),
      '#default_value' => $config->get('token'),
      '#attributes' => [
        'readonly' => 'readonly'
      ],
    ];

    $form['local']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate new token'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('remote_config_sync.settings');
    $token = $this->guidv4();
    $config->set('token', $token)
      ->save();
  }

  /**
   * Generate a new security token.
   *
   * @return string
   */
  protected function guidv4() {
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf(
      '%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)
    );
  }

}
