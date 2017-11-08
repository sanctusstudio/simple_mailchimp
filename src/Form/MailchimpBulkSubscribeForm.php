<?php

/**
 * @file
 * Contains \Drupal\simple_mailchimp\Form\MailchimpBulkSubscribeForm.
 */

namespace Drupal\simple_mailchimp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MailchimpBulkSubscribeForm.
 *
 * @package Drupal\simple_mailchimp\Form
 */
class MailchimpBulkSubscribeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailchimp_bulk_subscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['emails'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Emails'),
      '#default_value' => '',
      '#description' => $this->t('Enter one email per line.')
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Subscribe all emails')
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    //TODO: Parse emails and subscribe them to mailchimp.

  }

}
