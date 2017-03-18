<?php

namespace Drupal\Tests\twig_tweak\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests twig_tweak twig extension.
 *
 * @group twig_tweak
 */
class TwigTweakTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'twig_tweak',
    'twig_tweak_test',
    'views',
    'node',
    'block',
    'image',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->createContentType(['type' => 'page']);
    $this->createNode(['title' => 'Alpha']);
    $this->createNode(['title' => 'Beta']);
    $this->createNode(['title' => 'Gamma']);
  }

  /**
   * Tests output produced by the twig extension.
   */
  public function testOutput() {
    $this->drupalGet('<front>');

    // Test default views display.
    $xpath = '//div[@class = "tt-view-default"]';
    $xpath .= '//div[contains(@class, "view-twig-tweak-test") and contains(@class, "view-display-id-default")]';
    $xpath .= '/div[@class = "view-content"]//ul[count(./li) = 3]/li';
    $this->assertByXpath($xpath . '//a[contains(@href, "/node/1") and . = "Alpha"]');
    $this->assertByXpath($xpath . '//a[contains(@href, "/node/2") and . = "Beta"]');
    $this->assertByXpath($xpath . '//a[contains(@href, "/node/3") and . = "Gamma"]');

    // Test page_1 view display.
    $xpath = '//div[@class = "tt-view-page_1"]';
    $xpath .= '//div[contains(@class, "view-twig-tweak-test") and contains(@class, "view-display-id-page_1")]';
    $xpath .= '/div[@class = "view-content"]//ul[count(./li) = 3]/li';
    $this->assertByXpath($xpath . '//a[contains(@href, "/node/1") and . = "Alpha"]');
    $this->assertByXpath($xpath . '//a[contains(@href, "/node/2") and . = "Beta"]');
    $this->assertByXpath($xpath . '//a[contains(@href, "/node/3") and . = "Gamma"]');

    // Test view argument.
    $xpath = '//div[@class = "tt-view-page_1-with-argument"]';
    $xpath .= '//div[contains(@class, "view-twig-tweak-test")]';
    $xpath .= '/div[@class = "view-content"]//ul[count(./li) = 1]/li';
    $this->assertByXpath($xpath . '//a[contains(@href, "/node/1") and . = "Alpha"]');

    // Test entity default view mode.
    $xpath = '//div[@class = "tt-entity-default"]';
    $xpath .= '/article[contains(@class, "node") and not(contains(@class, "node--view-mode-teaser"))]';
    $xpath .= '/h2/a/span[. = "Alpha"]';
    $this->assertByXpath($xpath);

    // Test entity teaser view mode.
    $xpath = '//div[@class = "tt-entity-teaser"]';
    $xpath .= '/article[contains(@class, "node") and contains(@class, "node--view-mode-teaser")]';
    $xpath .= '/h2/a/span[. = "Alpha"]';
    $this->assertByXpath($xpath);

    // Test loading entity from url.
    $xpath = '//div[@class = "tt-entity-from-url" and . = ""]';
    $this->assertByXpath($xpath);
    $this->drupalGet('/node/2');
    $xpath = '//div[@class = "tt-entity-from-url"]';
    $xpath .= '/article[contains(@class, "node")]';
    $xpath .= '/h2/a/span[. = "Beta"]';
    $this->assertByXpath($xpath);

    // Test field.
    $xpath = '//div[@class = "tt-field"]/div[contains(@class, "field--name-body")]/p[. != ""]';
    $this->assertByXpath($xpath);

    // Test menu (default).
    $xpath = '//div[@class = "tt-menu-default"]/ul[@class = "menu"]/li/a[. = "Link 1"]/../ul[@class = "menu"]/li/ul[@class = "menu"]/li/a[. = "Link 3"]';
    $this->assertByXpath($xpath);

    // Test menu (level).
    $xpath = '//div[@class = "tt-menu-level"]/ul[@class = "menu"]/li/a[. = "Link 2"]/../ul[@class = "menu"]/li/a[. = "Link 3"]';
    $this->assertByXpath($xpath);

    // Test menu (depth).
    $xpath = '//div[@class = "tt-menu-depth"]/ul[@class = "menu"]/li[not(ul)]/a[. = "Link 1"]';
    $this->assertByXpath($xpath);

    // Test region.
    $xpath = '//div[@class = "tt-region"]';
    $xpath .= '/div[contains(@class, "block-page-title-block") and h1[@class="page-title" and . = "Beta"]]';
    $xpath .= '/following-sibling::div[@class="messages messages--warning" and contains(., "Hi!")]';
    $xpath .= '/following-sibling::div[contains(@class, "block-system-powered-by-block")]/span[. = "Powered by Drupal"]';
    $this->assertByXpath($xpath);

    // Test block.
    $xpath = '//div[@class = "tt-block"]';
    $xpath .= '/div[@id="block-powered-by-drupal"]/span[contains(., "Powered by Drupal")]';
    $this->assertByXpath($xpath);

    // Test token.
    $xpath = '//div[@class = "tt-token" and . = "Drupal"]';
    $this->assertByXpath($xpath);

    // Test token with context.
    $xpath = '//div[@class = "tt-token-data" and . = "Beta"]';
    $this->assertByXpath($xpath);

    // Test config.
    $xpath = '//div[@class = "tt-config" and . = "Anonymous"]';
    $this->assertByXpath($xpath);

    // Test status message.
    $xpath = '//div[@class = "messages messages--warning" and contains(., "Hi!")]';
    $this->assertByXpath($xpath);

    // Test token replacement.
    $xpath = '//div[@class = "tt-token-replace" and . = "Site name: Drupal"]';
    $this->assertByXpath($xpath);

    // Test preg replacement.
    $xpath = '//div[@class = "tt-preg-replace" and . = "foo-bar"]';
    $this->assertByXpath($xpath);

    // Test image style.
    $xpath = '//div[@class = "tt-image-style" and contains(., "styles/thumbnail/public/images/ocean.jpg")]';
    $this->assertByXpath($xpath);
  }

  /**
   * Checks that an element specified by a the xpath exists on the current page.
   */
  public function assertByXpath($xpath) {
    $this->assertSession()->elementExists('xpath', $xpath);
  }

  /**
   * {@inheritdoc}
   */
  protected function drupalGet($path, array $options = [], array $headers = []) {
    // Title block rendered through drupal_region() is cached by some reason.
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['block_view']);
    return parent::drupalGet($path, $options, $headers);
  }

}
