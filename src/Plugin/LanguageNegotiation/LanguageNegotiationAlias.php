<?php
/**
 * @file
 * Contains Drupal\alias_language_negotiation\Plugin\LanguageNegotiation.
 */


namespace Drupal\alias_language_negotiation\Plugin\LanguageNegotiation;

use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

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
class LanguageNegotiationAlias extends LanguageNegotiationMethodBase {

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
      // $container->get('path.alias_storage')
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

}
