<?php

/**
 * @file
 * Contains \Drupal\Core\Handler\HandlerTypeManagerInterface.
 */

namespace Drupal\Core\Handler;

/**
 * Defines an interface for handler type managers.
 */
interface HandlerTypeManagerInterface {

  /**
   * Gets handler types for the specified group.
   *
   * @param string $group
   *   The handler type group to retrieve.
   *
   * @return \Drupal\Core\Handler\HandlerTypeInterface[]
   *   Array of handler type plugins keyed by machine name.
   */
  public function getHandlerTypesByGroup($group);

  /**
   * Gets all the existing handler type groups.
   *
   * @return array
   *   Array of handler type group labels. Keyed by group name.
   */
  public function getGroups();

  /**
   * Gets all the providers for a specific handler type group.
   *
   * @param string $group
   *   The handler type group to retrieve.
   *
   * @return array
   *   An array keyed by provider name.
   */
  public function getGroupProviders($group);

}
