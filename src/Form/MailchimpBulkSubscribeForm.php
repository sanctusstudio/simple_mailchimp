<?php

/**
 * @file
 * Contains \Drupal\simple_mailchimp\Form\MailchimpBulkSubscribeForm.
 */

namespace Drupal\simple_mailchimp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_mailchimp\MailchimpInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MailchimpBulkSubscribeForm.
 *
 * @package Drupal\simple_mailchimp\Form
 */
class MailchimpBulkSubscribeForm extends FormBase {

  /**
   * @var \Drupal\simple_mailchimp\Mailchimp
   */
  protected $mailchimp;

  public function __construct(MailchimpInterface $mailchimp) {
    $this->mailchimp = $mailchimp;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('simple_mailchimp.mailchimp')
    );
  }

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

    $emails = explode('\n', $form['emails']['#value']);

    if (empty($emails)) {
      return;
    }
    $messages = [];

    foreach($emails as $email) {

      $response = $this->mailchimp->subscribeEmail($email);

      $messages[$response['status']][] = $response['message'];
    }

    if (!empty($messages)) {

      foreach($messages as $key => $message) {
        drupal_set_message($this->t('@message', [
          '@message' => implode('\n', $message)]), $key);
      }
    }

  }

}
