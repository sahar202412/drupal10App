<?php

namespace Drupal\forum\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the node is assigned only a "leaf" term in the forum taxonomy.
 *
 * @Constraint(
 *   id = "ForumLeaf",
 *   label = @Translation("Forum leaf", context = "Validation"),
 * )
 */
class ForumLeafConstraint extends Constraint {

  /**
   * Message for missing value.
   *
   * @var string
   */
  public $selectForum = 'Select a forum.';

  /**
   * Message for invalid selection.
   *
   * @var string
   */
  public $noLeafMessage = 'The item %forum is a forum container, not a forum. Select one of the forums below instead.';

}
