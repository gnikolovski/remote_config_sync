<?php

namespace Drupal\Tests\remote_config_sync\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the settings form.
 *
 * @group remote_config_sync
 */
class SettingsFormTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'remote_config_sync',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalLogin($this->drupalCreateUser(['access remote config sync admin']));
  }

  /**
   * Tests form structure.
   */
  public function testFormStructure() {
    $this->drupalGet('admin/config/development/remote-config-sync/settings');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->titleEquals('Settings | Drupal');
    $this->assertSession()->fieldExists('token');
    $this->assertSession()->checkboxNotChecked('disable_confirmation');
  }

  /**
   * Tests form access.
   */
  public function testFormAccess() {
    $this->drupalLogout();
    $this->drupalGet('admin/config/development/remote-config-sync/settings');
    $this->assertSession()->statusCodeEquals(403);
  }

}
