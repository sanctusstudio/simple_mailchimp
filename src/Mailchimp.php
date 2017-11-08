<?php

namespace Drupal\simple_mailchimp;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;

class Mailchimp implements MailchimpInterface {

  const MEMBER_STATUS_SUBSCRIBED = 'subscribed';
  const MEMBER_STATUS_UNSUBSCRIBED = 'unsubscribed';
  const MEMBER_STATUS_PENDING = 'pending';
  const MEMBER_STATUS_CLEANED = 'cleaned';

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  protected $api_key;

  protected $list_id;

  protected $data_center;

  protected $base_url;

  protected $success_msg;

  protected $already_subscribed_msg;

  protected $system_failure_msg;

  /**
   * Mailchimp constructor.
   *
   * @param \GuzzleHttp\ClientInterface $client
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $config_factory) {

    $this->client = $client;

    // Load mailchimp credentials via configuration system.
    $api_config = $config_factory->get('simple_mailchimp.settings');

    $this->api_key = $api_config->get('api_key');
    $this->list_id = $api_config->get('list_id');
    $this->data_center = $api_config->get('data_center');

    $this->base_url = 'https://' . $this->data_center . '.api.mailchimp.com/3.0/';

    // Load mailchimp response messages
    $this->success_msg = $api_config->get('success_msg');
    $this->already_subscribed_msg = $api_config->get('already_subscribed_msg');
    $this->system_failure_msg = $api_config->get('system_failure_msg');

  }

  /**
   * @param $email
   *
   * @return array
   */
  public function subscribeEmail($email) {
    $return = [];

    $subscriber_email = strtolower(trim($email));


    // check if already subscribed
    if($this->canSubscribe($subscriber_email)) {
      $return = $this->subscribe($subscriber_email);
    }
    else {
      $return['status'] = 'warning';
      $return['message'] = $this->already_subscribed_msg;

    }

    return $return;
  }

  /**
   * @param $email
   *
   * @return array
   */
  protected function subscribe($email) {
    $return = [];

    $subscribe_url = $this->base_url . 'lists/' . $this->list_id . '/members';

    try {
      $response = $this->client->request('POST', $subscribe_url, [
        'auth' => ['apikey', $this->api_key],
        'json' => [
          'email_address' => $email,
          'status' => self::MEMBER_STATUS_SUBSCRIBED,
        ]
      ]);

      $content = json_decode($response->getBody()->getContents(), TRUE);

      $return['status'] = 'status';
      $return['message'] = $this->success_msg;
      $return['content'] = $content;

    }
    catch(\Exception $e) {
      // TODO: throw a new exception that can be caught in calling method
      $return['status'] = 'error';
      $return['message'] = $this->system_failure_msg;
      $return['content'] = $e->getMessage();
    }

    return $return;
  }

  /**
   * @param $email
   *
   * @return bool
   */
  protected function canSubscribe($email) {
    $hash = md5($email);
    $url = $this->base_url . 'lists/' . $this->list_id . '/members/' . $hash;

    try {
      $response = $this->client->request('GET', $url, [
        'auth' => ['apikey', $this->api_key],
      ]);

      return ($response->getStatusCode()=='404');
    }
    catch(\Exception $e) {
      return TRUE;
    }

  }

  /**
   * @return array|mixed|null
   */
  public function getSuccessMsg() {
    return $this->success_msg;
  }

  /**
   * @return array|mixed|null
   */
  public function getAlreadySubscribedMsg() {
    return $this->already_subscribed_msg;
  }

  /**
   * @return array|mixed|null
   */
  public function getSystemFailureMsg() {
    return $this->system_failure_msg;
  }

}