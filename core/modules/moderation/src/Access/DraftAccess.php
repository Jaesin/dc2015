<?php
/**
 * @file
 * Contains \Drupal\moderation\Access\DraftAccess.
 */

namespace Drupal\moderation\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides an access checker for node revisions.
 */
class DraftAccess implements AccessInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The node access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $nodeAccess;

  /**
   * Constructs a new DraftAccess.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->nodeStorage = $entity_manager->getStorage('node');
    $this->nodeAccess = $entity_manager->getAccessControlHandler('node');
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, AccountInterface $account, NodeInterface $node = NULL) {
    // Check that the user has the ability to update the node, and that the node
    // has a draft.
    return AccessResult::allowedIf($node->access('update', $account) && moderation_node_has_draft($node));
  }

}
