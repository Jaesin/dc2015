<?php
/**
 * @file
 * Contains \Drupal\Core\Handler\HandlerType
 */

namespace Drupal\Core\Handler;

use Drupal\Core\Plugin\PluginBase;

/**
 * Default object used for handler type plugins.
 *
 * @see \Drupal\Core\Handler\HandlerTypeManager
 * @see plugin_api
 */
class HandlerType extends PluginBase implements HandlerTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t($this->pluginDefinition['label'], [], ['context' => 'handler_type']);
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->pluginDefinition['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup() {
    return $this->pluginDefinition['group'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefault() {
    return $this->pluginDefinition['default'];
  }

  /**
   * {@inheritdoc}
   */
  public function getInterface() {
    return !empty($this->pluginDefinition['interface']) ? $this->pluginDefinition['interface'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getProvider() {
    return $this->pluginDefinition['provider'];
  }
}
