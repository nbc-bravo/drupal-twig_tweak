<?php

/**
 * @file
 * Contains Drupal\twig_tweak\TwigExtension.
 */

namespace Drupal\twig_tweak;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\Token;

/**
 * A class providing Twig extension with some useful functions and filters.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * TwigExtension constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Token $token, ConfigFactoryInterface $config_factory) {
    $this->blockBuilder = \Drupal::entityTypeManager()
      ->getViewBuilder('block');

    $this->entityTypeManager = $entity_type_manager;
    $this->token = $token;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('drupal_view', [$this, 'drupalView']),
      new \Twig_SimpleFunction('drupal_block', [$this, 'drupalBlock']),
      new \Twig_SimpleFunction('drupal_token', [$this, 'drupalToken']),
      new \Twig_SimpleFunction('drupal_entity', [$this, 'drupalEntity']),
      new \Twig_SimpleFunction('drupal_config', [$this, 'drupalConfig'])
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('php', [$this, 'phpFilter']),
      new \Twig_SimpleFilter('token_replace', [$this, 'tokenReplaceFilter']),
      new \Twig_SimpleFilter('preg_replace', [$this, 'pregPeplaceFilter']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'twig_tweak';
  }

  /**
   * Embeds a view.
   */
  public function drupalView($name, $display_id = 'default') {
    $args = func_get_args();
    return views_embed_view(
      $name,
      $display_id,
      isset($args[2]) ? $args[2] : NULL,
      isset($args[3]) ? $args[3] : NULL,
      isset($args[4]) ? $args[2] : NULL
    );
  }

  /**
   * Builds the render array for the provided block.
   */
  public function drupalBlock($id) {
    $block = Block::load($id);
    return $this->entityTypeManager->getViewBuilder('block')->view($block);
  }

  /**
   * Replaces a given tokens with appropriate value.
   */
  public function drupalToken($token) {
    return $this->token->replace("[$token]");
  }

  /**
   * Returns the render array for an entity.
   */
  public function drupalEntity($entity_type, $id, $view_mode = NULL, $langcode = NULL) {
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($id);
    $render_controller = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    return $render_controller->view($entity, $view_mode, $langcode);
  }

  /**
   * Gets data from this configuration.
   */
  public function drupalConfig($name, $key) {
    return $this->configFactory->get($name)->get($key);
  }

  /**
   * Evaluates a string of PHP code.
   */
  public function phpFilter($code) {
    ob_start();
    print eval($code);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

  /**
   * Replaces all tokens in a given string with appropriate values.
   */
  public function tokenReplaceFilter($text) {
    return $this->token->replace($text);
  }

  /**
   * Performs a regular expression search and replace.
   */
  public function pregPeplaceFilter($text, $pattern, $replacement) {
    return preg_replace('/' . preg_quote($pattern, '/') . '/', $replacement, $text);
  }

}
