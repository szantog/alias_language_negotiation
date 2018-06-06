<?php
/**
 * @file
 * Contains Drupal\alias_language_negotiation\Plugin\LanguageNegotiation.
 */


namespace Drupal\alias_language_negotiation\Plugin\LanguageNegotiation;

use Drupal\Core\Path\AliasStorageInterface;
use Drupal\language\LanguageNegotiationMethodBase;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Language\LanguageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Class for identifying language based on an alias.
 *
 * @LanguageNegotiation(
 *   id = \Drupal\alias_language_negotiation\Plugin\LanguageNegotiation\LanguageNegotiationAlias::METHOD_ID,
 *   weight = -10,
 *   name = @Translation("Alias"),
 *   description = @Translation("Language from the alias.")
 * )
 */
class LanguageNegotiationAlias extends LanguageNegotiationMethodBase implements InboundPathProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-alias';

  /**
   * The alias storage.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * Constructs a new LanguageNegotiationAliasLanguage instance.
   *
   * @param \Drupal\Core\Path\AliasStorageInterface    $alias_storage
   *   The alias storage.
   */
  public function __construct(AliasStorageInterface $alias_storage) {
    $this->aliasStorage = $alias_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('path.alias_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    $alias = $this->loadAlias($request->getPathInfo());
    if (!$alias) {
      return NULL;
    }

    $langcode = $alias['langcode'];
    $language_enabled = array_key_exists($langcode, $this->languageManager->getLanguages());
    return $language_enabled ? $langcode : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $alias = $this->loadAlias($request->getPathInfo());

    return empty($alias['source']) ? $path : $alias['source'];
  }

  /**
   * Helper function to get an alias from a prefixed path.
   *
   * @param string $path_alias
   *   A resource path.
   *
   * @return array
   *   The alias array corresponding to $path_alias or FALSE.
   *   See AliasStorage::load()
   */
  protected function loadAlias($path_alias) {
    $unprefixed_path = $this->stripPathPrefix($path_alias);

    $conditions = ['alias' => $unprefixed_path];

    return $this->aliasStorage->load($conditions);
  }

  /**
   * Helper function to strip the language prefix from multilingual paths.
   *
   * @param string $path_info
   *   Path that might contain a language prefix.
   *
   * @return string
   *   Path without the language prefix.
   */
  protected function stripPathPrefix($path_info) {
    $parts = explode('/', trim($path_info, '/'));
    $prefix = array_shift($parts);
    if ($prefix == $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)
        ->getId()) {
      return '/' . implode('/', $parts);
    }

    return $path_info;
  }
}
