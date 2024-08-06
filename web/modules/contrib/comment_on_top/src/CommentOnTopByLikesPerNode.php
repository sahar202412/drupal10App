<?php declare(strict_types = 1);

  namespace Drupal\comment_on_top;

  use Drupal\Core\Database\Connection;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * Defines a service for managing sticking comment on top by likes for node.
   */
  final class CommentOnTopByLikesPerNode {

    /**
     * Database connection.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $database;

    public function __construct(Connection $database) {
      $this->database = $database;
    }

    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('database')
      );
    }


    /**
     * Set 'comment_on_top_by_likes' value 0 or 1 for node.
     *
     * @param int $nid
     *   The node ID.
     * @param int $checkBoxValue
     *   The node ID.
     */
    public function updateStickOnTopByLikes(int $nid, int $checkBoxValue): void {
      $this->database->merge('comment__stick_on_top_by_likes')
        ->key(['nid' => $nid])
        ->fields([
          'sticked_on_top_by_likes' => $checkBoxValue,
        ])
        ->execute();
    }


    /**
     * Get 'sticked_on_top_by_likes' value for the node.
     *
     * @param int $nid
     *   The node ID.
     *
     * @return int
     *   The value of 'sticked_on_top_by_likes'.
     */
    public function getStickedOnTopByLikes(int $nid) {
      return $this->database->select('comment__stick_on_top_by_likes', 'c')
        ->fields('c', ['sticked_on_top_by_likes'])
        ->condition('nid', $nid)
        ->execute()
        ->fetchField();
    }


    /**
     * Set 'comment_on_top_by_likes' to 0 for the node.
     *
     * @param int $nid
     *   The node ID.
     * @param string $type
     *   The type of the comment.
     */
    public function notStickOnTopByLikes(int $nid, string $type): void {
      $this->database->insert('comment__stick_on_top_by_likes')
        ->fields([
          'nid' => $nid,
          'type' => $type,
          'sticked_on_top_by_likes' => 0,
        ])
        ->execute();
    }


    /**
     * Update 'sticked_on_top_by_likes' value in the comment__stick_on_top_by_likes table by type.
     *
     * @param string $type
     *   The type of comment.
     * @param int $value
     *   The value to set for 'sticked_on_top_by_likes'.
     */
    public function updateStickedOnTopByLikesByType(string $type, int $value): void {
      $this->database->update('comment__stick_on_top_by_likes')
        ->fields(['sticked_on_top_by_likes' => $value])
        ->condition('type', $type)
        ->execute();
    }


    /**
     * Create a record in the comment__stick_on_top_by_likes table.
     *
     * @param int $nid
     *   The node ID.
     * @param int $checkBoxValue
     *   The value for 'sticked_on_top_by_likes'.
     * @param string $type
     *   The type of the comment.
     */
    public function createStickedOnTopByLikesRecord(int $nid, int $checkBoxValue, string $type): void {
      $this->database->insert('comment__stick_on_top_by_likes')
        ->fields([
          'nid' => $nid,
          'type' => $type,
          'sticked_on_top_by_likes' => $checkBoxValue,
        ])
        ->execute();
    }
  }
