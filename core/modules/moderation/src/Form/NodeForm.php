<?php
/**
 * @file
 * Contains \Drupal\moderation\Form\NodeForm
 */

namespace Drupal\moderation\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeForm as BaseNodeForm;

/**
 * Override the node form.
 */
class NodeForm extends BaseNodeForm {

  /**
   * Track if this is a draft.
   *
   * @var bool
   */
  protected $isDraft = FALSE;

  /**
   * Ensure proper node revision is used in the node form.
   *
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getEntity();
    if (!$node->isNew() && $node->type->entity->isNewRevision() && $revision_id = moderation_node_has_draft($node)) {
      /** @var \Drupal\node\NodeStorage $storage */
      $storage = \Drupal::service('entity.manager')->getStorage('node');
      $this->entity = $storage->loadRevision($revision_id);
      $this->isDraft = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->getEntity();
    if (!$node->isNew() && $node->type->entity->isNewRevision() && $node->isPublished()) {
      // Add a 'save as draft' action.
      $element['draft'] = $element['submit'];
      $element['draft']['#access'] = TRUE;
      $element['draft']['#dropbutton'] = 'save';
      $element['draft']['#value'] = $this->t('Save as draft');
      // Setting to draft must be called before ::save, while setting the
      // redirect must be done after.
      array_unshift($element['draft']['#submit'], '::draft');
      $element['draft']['#submit'][] = '::setRedirect';

      // Put the draft button first.
      $element['draft']['#weight'] = -10;

      // If the user doesn't have 'administer nodes' permission, and this is
      // a published node in a type that defaults to being unpublished, then
      // only allow new drafts.
      if (!\Drupal::currentUser()->hasPermission('administer nodes') && $this->nodeTypeUnpublishedDefault()) {
        $element['submit']['#access'] = FALSE;
        unset($element['draft']['#dropbutton']);
      }
    }

    // If this is an existing draft, change the publish button text.
    if ($this->isDraft && isset($element['publish'])) {
      $element['publish']['#value'] = t('Save and publish');
    }

    return $element;
  }

  /**
   * Save node as a draft.
   */
  public function draft(array $form, FormStateInterface $form_state) {
    $this->entity->isDefaultRevision(FALSE);
  }

  /**
   * Set default revision if this was previously a draft, and is now being
   * published.
   *
   * {@inheritdoc}
   */
  public function publish(array $form, FormStateInterface $form_state) {
    $node = parent::publish($form, $form_state);
    if ($this->isDraft) {
      $node->isDefaultRevision(TRUE);
    }
  }

  /**
   * Set a redirect to the draft.
   */
  public function setRedirect(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect(
      'node.draft',
      array('node' => $this->getEntity()->id())
    );
  }

  /**
   * Helper function to determine unpublished default for a node type.
   *
   * @return bool
   *   Returns TRUE if the current node type is set to unpublished by default.
   */
  protected function nodeTypeUnpublishedDefault() {
    $type = $this->getEntity()->getType();
    // @todo Make it possible to get default values without an entity.
    //   https://www.drupal.org/node/2318187
    $node = $this->entityManager->getStorage('node')->create(array('type' => $type));
    return !$node->isPublished();
  }

}


