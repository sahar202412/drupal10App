<?php

  declare(strict_types = 1);

  namespace Drupal\comment_on_top;

  use Drupal\comment_on_top\CommentOnTopByLikesPerNode;
  use Drupal\Core\Entity\EntityTypeManagerInterface;
  use Drupal\Core\Messenger\MessengerInterface;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * Service for managing comments and their stickiness.
   */
  final class CommentOnTopService {

    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * The messenger service.
     *
     * @var \Drupal\Core\Messenger\MessengerInterface
     */
    protected $messenger;

    /**
     * The CommentOnTopByLikesPerNode service.
     *
     * @var \Drupal\comment_on_top\CommentOnTopByLikesPerNode
     */
    protected $commentOnTopByLikesPerNode;

    /**
     * Constructs a new CommentOnTopService object.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
     *   The entity type manager.
     * @param \Drupal\Core\Messenger\MessengerInterface $messenger
     *   The messenger service.
     * @param \Drupal\comment_on_top\CommentOnTopByLikesPerNode $comment_on_top_by_likes_per_node
     *   The CommentOnTopByLikesPerNode service.
     */
    public function __construct(EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger,
                                CommentOnTopByLikesPerNode $comment_on_top_by_likes_per_node) {
      $this->entityTypeManager = $entityTypeManager;
      $this->messenger = $messenger;
      $this->commentOnTopByLikesPerNode = $comment_on_top_by_likes_per_node;
    }

    /**
     * Creates a new instance of the CommentOnTopService.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *   The container.
     *
     * @return static
     *   A new instance of this class.
     */
    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('entity_type.manager'),
        $container->get('messenger'),
        $container->get('comment_on_top_by_likes.node')
      );
    }

    /**
     * Change all comments in a node from "1" to null in field stick on top.
     *
     * @param int $node_id
     *   The ID of the node.
     */
    public function changeAllLastCommentsToNull($node_id) {
      $comments = $this->entityTypeManager->getStorage('comment')->loadByProperties([
        'entity_id' => $node_id,
        'entity_type' => 'node',
      ]);

      foreach ($comments as $comment) {
        if ($comment->field_stick_comment_on_top_boole->value === '1') {
          $comment->field_stick_comment_on_top_boole->setValue(null);
          $comment->save();
        }
      }
    }

    /**
     * Stick a comment on top of others in a node.
     *
     * @param int $node_id
     *   The ID of the node.
     * @param int $comment_id
     *   The ID of the comment.
     */
    public function stickOnTop($node_id, $comment_id) {
      $this->changeAllLastCommentsToNull($node_id);

      //If comments exists in this node, only then you can use stickOnTop()
      $comment_id = (int) $comment_id;
      if ($comment_id > 0) {
        $comment = $this->entityTypeManager->getStorage("comment")->load($comment_id) ?? null;
        if ($comment) {
          $comment->field_stick_comment_on_top_boole->value = '1';
          $comment->save();
        }
      }
      //If on node is checked "Stick on top by likes" then display message
      $isStickedOnTopByLikes = $this->commentOnTopByLikesPerNode->getStickedOnTopByLikes(intval($node_id));
      if ($isStickedOnTopByLikes == 0) {
        $this->messenger->addStatus(t('Comment is successfully sticked on top!'));
      }
    }

    /**
     * Remove a comment from being on top in a node.
     *
     * @param int $node_id
     *   The ID of the node.
     * @param int $comment_id
     *   The ID of the comment.
     */
    public function removeFromTop($node_id, $comment_id) {
      $comment = $this->entityTypeManager->getStorage("comment")->load($comment_id);
      $comment->field_stick_comment_on_top_boole->value = null;
      $comment->save();

      $this->messenger->addStatus(t('Comment is removed from top!'));
    }
  }
