<?php

namespace Drupal\remote_config_sync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RemoteForm.
 */
class RemoteForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'remote_config_sync_form';
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

    $form['remote'] = [
      '#type' => 'fieldset',
    ];

    $form['remote']['notice'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Enter your remote site details. In order to push the configuration, you need to enter the remote site URL and your security token for that site.'),
    ];

    $form['remote']['remotes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Remote sites'),
      '#required' => TRUE,
      '#description' => $this->t('Format: url | token. Enter one site per line.'),
      '#default_value' => $config->get('remotes'),
      '#attributes' => [
        'placeholder' => t('http://myremotesite.com|17254323-55f8-4645-9066-0d439ad3f545'),
      ],
    ];

    $form['remote']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $remotes = explode("\r\n", $form_state->getValue('remotes'));
    foreach ($remotes as $remote) {
      if ($remote && strpos($remote, '|') === FALSE) {
        $form_state->setErrorByName('remotes',
          $this->t('Please separate url and token with a pipe "|" character.')
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('remote_config_sync.settings');
    $config->set('remotes', $form_state->getValue('remotes'))
      ->save();
  }

}
