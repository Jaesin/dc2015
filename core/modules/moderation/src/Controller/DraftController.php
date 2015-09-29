<?php
/**
 * @file
 * Contains \Drupal\moderation\Controller\DraftController.
 */

namespace Drupal\moderation\Controller;

use Drupal\node\Controller\NodeController;
use Drupal\node\NodeInterface;

/**
 * Page controller for viewing node drafts.
 */
class DraftController extends NodeController {

  /**
   * Display current revision denoted as a draft.
   *
   * @param \Drupal\node\NodeInterface
   *   The current node.
   */
  public function show(NodeInterface $node) {
    return $this->revisionShow(moderation_node_has_draft($node));
  }

  /**
   * Display the title of the draft.
   */
  public function draftPageTitle(NodeInterface $node) {
    return $this->revisionPageTitle(moderation_node_has_draft($node));
  }

}
