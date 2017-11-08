<?php

/**
 * @file
 * Contains \Drupal\simple_mailchimp\Plugin\Block\MailchimpSubscribeBlock.
 */

namespace Drupal\simple_mailchimp\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_mailchimp\Form\MailchimpSubscribeForm;

/**
 * Provides a 'MailchimpSubscribeBlock' block.
 *
 * @Block(
 *  id = "mailchimp_subscribe_block",
 *  admin_label = @Translation("Mailchimp Subscribe Block"),
 * )
 */
class MailchimpSubscribeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['subheading'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Subheading'),
      '#default_value' => isset($this->configuration['subheading']) ? $this->configuration['subheading'] : '',
      '#maxlength' => 255,
    );
    $form['email_label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email Label'),
      '#default_value' => isset($this->configuration['email_label']) ? $this->configuration['email_label'] : 'Email',
      '#maxlength' => 255,
    );
    $form['email_placeholder'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email Placeholder'),
      '#default_value' => isset($this->configuration['email_placeholder']) ? $this->configuration['email_placeholder'] : 'example@example.com',
      '#maxlength' => 255,
    );
    $form['email_description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email description'),
      '#default_value' => isset($this->configuration['email_description']) ? $this->configuration['email_description'] : '',
      '#maxlength' => 255,
    );
    $form['button_label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Button Label'),
      '#default_value' => isset($this->configuration['button_label']) ? $this->configuration['button_label'] : $this->t('Subscribe'),
      '#maxlength' => 255,
    );

    return $form;
  }



  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['subheading'] = $form_state->getValue('subheading');
    $this->configuration['email_label'] = $form_state->getValue('email_label');
    $this->configuration['email_placeholder'] = $form_state->getValue('email_placeholder');
    $this->configuration['email_description'] = $form_state->getValue('email_description');
    $this->configuration['button_label'] = $form_state->getValue('button_label');
  }
  
  /**
   * {@inheritdoc}
   */
  public function build() {

    // Fetch form
    $form = \Drupal::formBuilder()->getForm(MailchimpSubscribeForm::class);
    $form['email']['#title'] = $this->configuration['email_label'];
    $form['email']['#placeholder'] = $this->configuration['email_placeholder'];
    $form['email']['#description'] = $this->configuration['email_description'];
    $form['actions']['submit']['#value'] =  $this->configuration['button_label'];

    // Build render array
    $output = array(
      '#theme' => 'simple_mailchimp_subscribe',
      '#subheading' => $this->configuration['subheading'],
      '#form' => $form
    );

    return $output;
  }
}
