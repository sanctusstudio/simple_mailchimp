<?php

/**
 * @file
 * Contains \Drupal\simple_mailchimp\Form\MailchimpSubscribeForm.
 */

namespace Drupal\simple_mailchimp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MailchimpSubscribeForm.
 *
 * @package Drupal\simple_mailchimp\Form
 */
class MailchimpSubscribeForm extends FormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_subscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['email'] = array(
      '#type' => 'email',
      '#size' => '22',
      '#required' => TRUE,
      '#attributes' => array(
        'class' => array('simple-mailchimp--email-field')
      )
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#attributes' => array(
        'class' => array('simple-mailchimp--submit-button')
      )
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //TODO: Move the code below to a service so this functionality can be reused by the Bulk Subscription form.

    // Instantiate a Guzzle client
    $client = \Drupal::httpClient();

    // Load mailchimp credentials via configuration system.
    $api_config = \Drupal::config('simple_mailchimp.mailchimp');
    $mailchimp_api_key = $api_config->get('api_key');
    $mailchimp_list_id = $api_config->get('list_id');
    $mailchimp_data_center = $api_config->get('data_center');

    $mailchimp_base_url = 'https://' . $mailchimp_data_center . '.api.mailchimp.com/3.0/';
    $subscriber_email = strtolower(trim($form['email']['#value']));
    $mailchimp_subscribe_url = $mailchimp_base_url . 'lists/' . $mailchimp_list_id . '/members';

    try {
      $response = $client->request('POST', $mailchimp_subscribe_url, [
        'auth' => ['apikey', $mailchimp_api_key],
        'json' => [
          'email_address' => $subscriber_email,
          'status' => 'subscribed',
        ]
      ]);
    }
    catch(\Exception $e) {
    }

    // Load mailchimp form-submission messages
    $on_success = $api_config->get('success_msg');
    $on_already_subscribed = $api_config->get('already_subscribed_msg');
    $on_system_failure = $api_config->get('system_failure_msg');

    if (isset($response) && $response->getStatusCode() == '200') {
      drupal_set_message($this->t('@message', array('@message' => $on_success)), 'status');
    }
    else if ($e->getCode() == '400') {
      drupal_set_message($this->t('@message', array('@message' => $on_already_subscribed)), 'warning');
    }
    else {
      $form_state->setRedirect('contact.site_page');
      drupal_set_message($this->t('@message', array('@message' => $on_system_failure)), 'error');
    }

  }

}
