<?php

namespace Drupal\Tests\simple_mailchimp\Functional;

use Drupal\simple_mailchimp\MailchimpInterface;
use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Tests for the Simple Mailchimp module.
 *
 * @group simple_mailchimp
 */
class MailchimpTests extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'system',
    //'views',
    //'field',
    'user',
    'simple_mailchimp',
    ];

  /**
   * A simple user.
   *
   * @var object
   */
  private $user;

  /**
   * Perform initial setup tasks that run before every test method.
   */
  public function setUp() {
    parent::setUp();

  }

  public function testNoPermission() {
    $this->user = $this->drupalCreateUser([
      //'access administration pages',
    ]);
    $this->drupalLogin($this->user);

    $this->drupalGet('admin/config/services/simple-mailchimp');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('admin/config/services/simple-mailchimp/bulk-subscribe');
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testWithPermission() {

    $perms = [
      'access administration pages',
      'mailchimp configuration',
      'mailchimp bulk subscribe',
    ];
    $this->user = $this->drupalCreateUser($perms);

    $this->drupalLogin($this->user);
  }

  /**
   * Tests that the Mailchimp settings page can be reached.
   */
  public function testSettingsPageExistsWithDefaultValues() {

    $this->testWithPermission();

    // page can be reached
    $this->drupalGet('admin/config/services/simple-mailchimp');
    $this->assertSession()->statusCodeEquals(200);

    // test default values
    $page = $this->getSession()->getPage();
    $field = $page->findField('api_key');
    $this->assertTrue($field->getValue() == '');

    $field = $page->findField('list_id');
    $this->assertTrue($field->getValue() == '');

    $field = $page->findField('data_center');
    $this->assertTrue($field->getValue() == '');

    $field = $page->findField('success_msg');
    $this->assertTrue($field->getValue() == 'Thank you for subscribing to our site!');
    $field = $page->findField('already_subscribed_msg');
    $this->assertTrue($field->getValue() == 'You were already subscribed to our list.');
    $field = $page->findField('system_failure_msg');
    $this->assertTrue($field->getValue() == 'Our system was unable to subscribe you to our mailing list.');

  }

  public function testSettingsPageUpdatesValuesOnSubmit() {

    $this->testWithPermission();

    $this->drupalGet('admin/config/services/simple-mailchimp');
    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getSession()->getPage();

    // for brevity, only testing the blank fields
    $page->fillField('api_key', 'This is my api key');
    $page->fillField('list_id', 'List id 1');
    $page->fillField('data_center', 'dc1');
    $this->submitForm([], 'edit-submit');


    // get settings page with updated values
    $this->drupalGet('admin/config/services/simple-mailchimp');

    $page = $this->getSession()->getPage();
    $field = $page->findField('api_key');
    $this->assertTrue($field->getValue() == 'This is my api key');

    $field = $page->findField('list_id');
    $this->assertTrue($field->getValue() == 'List id 1');

    $field = $page->findField('data_center');
    $this->assertTrue($field->getValue() == 'dc1');
  }

  /**
   * Tests that the Mailchimp bulk subscribe page can be reached.
   */
  public function testBulkSubscribePageExists() {

    $this->testWithPermission();

    // page can be reached
    $this->drupalGet('admin/config/services/simple-mailchimp/bulk-subscribe');
    $this->assertSession()->statusCodeEquals(200);


    $page = $this->getSession()->getPage();
    $field = $page->findField('emails');
    $this->assertTrue($field->getValue() == '');

  }

  /**
   * Tests the functionality of the Mailchimp Subscribe block
   */
  public function testSubscribeBlock() {
    $this->markTestIncomplete('Not sure how to test the block');

    // create a user with minimal privilege
    $user = $this->drupalCreateUser([
      'access administration pages',
      //'administer blocks',
      ]);
    $this->drupalLogin($user);

    // Add a MailchimpSubscribeBlock
    $block = [
      'settings[label]' => $this->randomMachineName(8),
      'id'     => 'mailchimp_subscribe_block',
      'theme'  => $this->config('system.theme')->get('default'),
      'region' => 'sidebar_first',
    ];

    $edit = [
      'settings[label]' => $block['settings[label]'],
      'id'     => $block['id'],
      'region' => $block['region']
    ];

    $url = 'admin/structure/block/add/' . $block['id'] . '/' . $block['theme'];
    $this->drupalPostForm($url, $edit, 'Save blocks');

    $default_text = 'The block configuration has been saved.';
    $this->assertSession()->pageTextContains($default_text);

  }

}
