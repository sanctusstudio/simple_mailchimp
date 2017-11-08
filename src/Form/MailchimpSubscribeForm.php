<?php

/**
 * @file
 * Contains \Drupal\simple_mailchimp\Form\MailchimpSubscribeForm.
 */

namespace Drupal\simple_mailchimp\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_mailchimp\MailchimpInterface;

/**
 * Class MailchimpSubscribeForm.
 *
 * @package Drupal\simple_mailchimp\Form
 */
class MailchimpSubscribeForm extends FormBase {

  /**
   * @var \Drupal\simple_mailchimp\MailchimpInterface
   */
  protected $mailchimp;

  public function __construct(MailchimpInterface $mailchimp) {
    $this->mailchimp = $mailchimp;
  }

  /**
   * {@inheritdoc}
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

    $response = $this->mailchimp->subscribeEmail($form['email']['#value']);

    $message = $this->t('@message', ['@message' => $response['$message']]);
    drupal_set_message($message, $response['status']);
  }

}
