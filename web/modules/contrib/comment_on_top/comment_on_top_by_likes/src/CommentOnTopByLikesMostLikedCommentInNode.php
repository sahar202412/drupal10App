<?php declare(strict_types = 1);

  namespace Drupal\comment_on_top_by_likes;

  use Drupal\Core\Database\Connection;
  use Symfony\Component\DependencyInjection\ContainerInterface;

	class CommentOnTopByLikesMostLikedCommentInNode	{


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
     * Get most liked comment in the node which is not reply/thread.
     *
     * @param int $nid
     *   The node ID.
     *
     * @return int
     *   The value of 'sticked_on_top_by_likes'.
     */
    public function getMostLikedCommentInNode(int $nid) {
      // Get array of cid-s for the current node which are not replies/threads
      $queryCommentFieldData = $this->database->select('comment_field_data', 'cfd')
        ->fields('cfd', ['cid'])
        ->condition('cfd.entity_id', $nid, '=')
        ->condition('cfd.pid', NULL, 'IS');
      $commentsCurrentNodeWhichNotReplies = $queryCommentFieldData->execute()->fetchCol();

      // Initialize variables
      $mostLikedCommentId = NULL;
      $highestNumberOfCalculatedLikes = NULL;

      // Loop through each comment
      foreach ($commentsCurrentNodeWhichNotReplies as $commentId) {
        // Get the number of likes and dislikes for the comment
        $queryLikeDislike = $this->database->select('comment__field_like_dislike', 'cld')
          ->fields('cld', ['field_like_dislike_likes', 'field_like_dislike_dislikes'])
          ->condition('cld.entity_id', $commentId, '=');
        $likeDislikeData = $queryLikeDislike->execute()->fetchAssoc();

        // Calculate the number of calculated likes
        $numberOfCalculatedLikes = intval($likeDislikeData['field_like_dislike_likes'])
          - intval($likeDislikeData['field_like_dislike_dislikes']);

        // If this comment has more calculated likes than the current most liked comment, update the most liked comment
        if ($highestNumberOfCalculatedLikes === NULL || $numberOfCalculatedLikes > $highestNumberOfCalculatedLikes) {
          $mostLikedCommentId = $commentId;
          $highestNumberOfCalculatedLikes = $numberOfCalculatedLikes;
        }
      }

      // Return the cid of the most liked comment
      return $mostLikedCommentId;
    }
	}
