<?php

namespace Drupal\Tests\simple_mailchimp\Unit;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\Config;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\simple_mailchimp\Mailchimp;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Tests generation of simple_mailchimp services.
 *
 * @coversDefaultClass \Drupal\simple_mailchimp\Mailchimp
 * @group simple_mailchimp
 */
class MailchimpTest extends UnitTestCase {


  protected $container;

  /**
   * @var \GuzzleHttp\Handler\MockHandler
   */
  protected $handler;

  /**
   * @var
   */
  protected $http_client;

  /**
   * Mock of config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config_factory;

  /**
   * @var \Drupal\simple_mailchimp\Mailchimp
   */
  protected $service;

  public function setUp() {
    parent::setUp();

    $this->config_factory = $this->getConfigFactoryStub([
      'simple_mailchimp.settings' => [
        'api_key' => 'test',
        'list_id' => 'list1',
        'data_center' => 'dc1',
        'success_msg' => 'Thank you for subscribing to our site!',
        'already_subscribed_msg' => 'You were already subscribed to our list.',
        'system_failure_msg' => 'Our system was unable to subscribe you to our mailing list.']
    ]);

    // Create a dummy container.
    $this->container = new ContainerBuilder();
    $this->container->set('config.factory', $this->config_factory);

    \Drupal::setContainer($this->container);

    // create a mock http client and file handler to handle mock responses
    $this->handler = new MockHandler();
    $stack = HandlerStack::create($this->handler);

    $this->http_client = new HttpClient(['handler' => $stack]);

  }

  public function testSubscribeEmail() {

    // instantiate the mailchimp service
    $this->service = new Mailchimp($this->http_client, $this->config_factory);

    $response_array = [
      'email_address' => 'test@example.com',
      'status' => 'subscribed',
    ];



    $check = new Response(404, [], 'not in list');
    $response = new Response(200, [], \GuzzleHttp\json_encode($response_array));
    $this->handler->append($check, $response);

    $this->service = new Mailchimp($this->http_client, $this->config_factory);

    $this->assertInstanceOf(Mailchimp::class, $this->service);
    $this->assertAttributeEquals('test', 'api_key', $this->service, 'api_key is not correct');
    $this->assertAttributeEquals('list1', 'list_id', $this->service, 'list_id is not correct');
    $this->assertAttributeEquals('dc1', 'data_center', $this->service, 'data_center is not correct');

    $actual = $this->service->subscribeEmail('test@example.com');

    $this->assertEquals($actual['message'], $this->service->getSuccessMsg(), print_r($actual, true));

  }

  public function testSubscribeEmailWithBadEmailAddress() {
    // instantiate the mailchimp service
    $this->service = new Mailchimp($this->http_client, $this->config_factory);

    $response_array = [
      'email_address' => 'test@example.com',
      'status' => 'subscribed',
    ];

    $check = new Response(200, [], 'In list');
    $this->handler->append($check);

    $this->service = new Mailchimp($this->http_client, $this->config_factory);

    $this->assertInstanceOf(Mailchimp::class, $this->service);
    $this->assertAttributeEquals('test', 'api_key', $this->service, 'api_key is not correct');
    $this->assertAttributeEquals('list1', 'list_id', $this->service, 'list_id is not correct');
    $this->assertAttributeEquals('dc1', 'data_center', $this->service, 'data_center is not correct');

    $actual = $this->service->subscribeEmail('test@example.com');

    $this->assertEquals($actual['message'], $this->service->getAlreadySubscribedMsg(), print_r($actual, true));
  }
}
