<?php
/**
 * @file
 * Contains \Drupal\Core\Handler\HandlerTypeInterface.
 */

namespace Drupal\Core\Handler;

/**
 * Interface for Handler Type plugins.
 */
interface HandlerTypeInterface {

  /**
   * Returns the translated label.
   *
   * @return string
   *   The translated label.
   */
  public function getLabel();

  /**
   * Returns the path to use for handler discovery.
   *
   * @return int
   *   The weight.
   */
  public function getPath();

  /**
   * Returns the default plugin id.
   *
   * @return string
   *   The default plugin id.
   */
  public function getDefault();

  /**
   * Returns the interface class.
   *
   * @return array
   *   The interface class.
   */
  public function getInterface();

  /**
   * Returns the provider.
   *
   * @return string
   *   The provider.
   */
  public function getProvider();

  /**
   * Returns the handler type group.
   *
   * @return string
   *   The handler type group.
   */
  public function getGroup();

}
