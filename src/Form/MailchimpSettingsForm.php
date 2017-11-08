<?php

/**
 * @file
 * Contains \Drupal\simple_mailchimp\Form\MailchimpSettingsForm.
 */

namespace Drupal\simple_mailchimp\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MailchimpSettingsForm.
 *
 * @package Drupal\simple_mailchimp\Form
 */
class MailchimpSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simple_mailchimp.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('simple_mailchimp.settings');

    $form['api_info'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('API Information'),
    );
    $form['api_info']['api_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Mailchimp API Key'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('api_key'),
    );
    $form['api_info']['list_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Mailchimp List ID'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('list_id'),
    );
    $form['api_info']['data_center'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Mailchimp Data Center'),
      '#description' => $this->t('The last part of your MailChimp API key (i.e. us6).'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('data_center'),
    );

    $form['messages'] = array(
      '#type' => 'fieldset',
      '#title' => 'Messages',
    );
    $form['messages']['success_msg'] = array(
      '#type' => 'textarea',
      '#title' => t('Successful subscription'),
      '#default_value' => $config->get('success_msg'),
    );
    $form['messages']['already_subscribed_msg'] = array(
      '#type' => 'textarea',
      '#title' => t('Already subscribed user'),
      '#default_value' => $config->get('already_subscribed_msg'),
    );
    $form['messages']['system_failure_msg'] = array(
      '#type' => 'textarea',
      '#title' => t('System Failure'),
      '#default_value' => $config->get('system_failure_msg'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('simple_mailchimp.mailchimp')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('list_id', $form_state->getValue('list_id'))
      ->set('data_center', $form_state->getValue('data_center'))
      ->set('success_msg', $form_state->getValue('success_msg'))
      ->set('already_subscribed_msg', $form_state->getValue('already_subscribed_msg'))
      ->set('system_failure_msg', $form_state->getValue('system_failure_msg'))
      ->save();
  }

}
