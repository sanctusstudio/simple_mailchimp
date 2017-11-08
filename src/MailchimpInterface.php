<?php

namespace Drupal\simple_mailchimp;

interface MailchimpInterface {

  public function subscribeEmail($email);
}
