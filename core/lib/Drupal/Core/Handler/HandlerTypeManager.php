<?php
/**
 * @file
 * Contains \Drupal\Core\Handler\HandlerTypeManager.
 */

namespace Drupal\Core\Handler;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Defines a handler type plugin manager to deal with handler type definitions.
 *
 * Extensions can define handler types in a EXTENSION_NAME.handler.types.yml
 * file contained in the extension's base directory. Each handler type has the
 * following structure:
 * @code
 *   MACHINE_NAME:
 *     label: STRING
 *     path: STRING
 *     group: STRING
 *     default: STRING
 *     interface: STRING
 * @endcode
 * For example:
 * @code
 * views.field_data_handler:
 *   label: Views Field Data Handler
 *   path: Field/FieldHandler
 *   group: views
 *   default: 'views_default_field_data'
 *   interface: '\Drupal\views\ViewsFieldDataHandlerInterface'
 * @endcode
 * Optionally a handler type can provide a group key. By default an extensions
 * handler type will be placed in a group labelled with the extension name.
 *
 * @see \Drupal\Core\Handler\HandlerType
 * @see \Drupal\Core\Handler\HandlerTypeInterface
 * @see plugin_api
 */
class HandlerTypeManager extends DefaultPluginManager implements HandlerTypeManagerInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    // Human readable label for the handler type.
    'label' => '',
    // The sub-directory to search for plugins.
    'path' => 'Plugin/Handler',
    // The handler group.
    'group' => '',
    // The plugin id of the default plugin (to be used as a fallback).
    'default' => '',
    // The Interface to for the handler (Will be enforced if set).
    'interface' => '',
    // The plugin id. Set by the plugin system based on the top-level YAML key.
    'id' => '',
  ];

  /**
   * Static cache of handlers keyed by group.
   *
   * @var array
   */
  protected $handlersByGroup;

  /**
   * The plugin instances.
   *
   * @var array
   */
  protected $instances = [];

  /**
   * Constructs a new HandlerTypeManager instance.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend, TranslationInterface $string_translation) {
    $this->factory = new ContainerFactory($this);
    $this->moduleHandler = $module_handler;
    $this->setStringTranslation($string_translation);
    $this->alterInfo('handler_types');
    $this->setCacheBackend($cache_backend, 'handler_types', ['handler_types']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new ContainerDerivativeDiscoveryDecorator(new YamlDiscovery('handler.types', $this->moduleHandler->getModuleDirectories()));
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    // Allow custom groups and therefore more than one group per extension.
    if (empty($definition['group'])) {
      $definition['group'] = $definition['provider'];
    }
    // Ensure a 1x multiplier exists.
    if (!in_array('1x', $definition['multipliers'])) {
      $definition['multipliers'][] = '1x';
    }
    // Ensure that multipliers are sorted correctly.
    sort($definition['multipliers']);
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return $this->moduleHandler->moduleExists($provider);
  }

  /**
   * {@inheritdoc}
   */
  public function getHandlerTypesByGroup($group) {
    if (!isset($this->handlersByGroup[$group])) {
      if ($cache = $this->cacheBackend->get($this->cacheKey . ':' . $group)) {
        $this->handlersByGroup[$group] = $cache->data;
      }
      else {
        $handler_types = [];
        foreach ($this->getDefinitions() as $plugin_id => $plugin_definition) {
          if ($plugin_definition['group'] == $group) {
            $handler_types[$plugin_id] = $plugin_definition;
          }
        }
        uasort($handler_types, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
        $this->cacheBackend->set($this->cacheKey . ':' . $group, $handler_types, Cache::PERMANENT, ['handler_types']);
        $this->handlersByGroup[$group] = $handler_types;
      }
    }

    $instances = [];
    foreach ($this->handlersByGroup[$group] as $plugin_id => $definition) {
      if (!isset($this->instances[$plugin_id])) {
        $this->instances[$plugin_id] = $this->createInstance($plugin_id);
      }
      $instances[$plugin_id] = $this->instances[$plugin_id];
    }
    return $instances;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    // Use a double colon so as to not clash with the cache for each group.
    if ($cache = $this->cacheBackend->get($this->cacheKey . '::groups')) {
      $groups = $cache->data;
    }
    else {
      $groups = [];
      foreach ($this->getDefinitions() as $plugin_definition) {
        if (!isset($groups[$plugin_definition['group']])) {
          $groups[$plugin_definition['group']] = $plugin_definition['group'];
        }
      }
      $this->cacheBackend->set($this->cacheKey . '::groups', $groups, Cache::PERMANENT, ['handler_types']);
    }
    // Get the labels. This is not cache-able due to translation.
    $group_labels = [];
    foreach ($groups as $group) {
      $group_labels[$group] =  $this->getGroupLabel($group);
    }
    asort($group_labels);
    return $group_labels;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupProviders($group) {
    $providers = [];
    $handler_types = $this->getHandlerTypesByGroup($group);
    foreach ($handler_types as $handler_type) {
      $provider = $handler_type->getProvider();
      $extension = FALSE;
      if ($this->moduleHandler->moduleExists($provider)) {
        $extension = $this->moduleHandler->getModule($provider);
      }
      if ($extension) {
        $providers[$extension->getName()] = $extension->getType();
      }
    }
    return $providers;
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    parent::clearCachedDefinitions();
    $this->handlersByGroup = NULL;
    $this->instances = [];
  }

  /**
   * Gets the label for a handler type group.
   *
   * @param string $group
   *   The handler type group.
   *
   * @return string
   *   The label.
   */
  protected function getGroupLabel($group) {
    // Extension names are not translatable.
    if ($this->moduleHandler->moduleExists($group)) {
      $label = $this->moduleHandler->getName($group);
    }
    else {
      // Custom group label that should be translatable.
      $label = $this->t($group, [], ['context' => 'handler_types']);
    }
    return $label;
  }
}
