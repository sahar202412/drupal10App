<?php declare(strict_types = 1);

namespace Drupal\comment_on_top_by_likes\Controller;

use Drupal\comment_on_top\CommentOnTopService;
use Drupal\comment_on_top_by_likes\CommentOnTopByLikesMostLikedCommentInNode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Sticks on top most liked comment on top.
 * It uses method from module stick_on_top service comment_on_top.service
 */
final class CommentOnTopByLikesController extends ControllerBase {

  /**
   * The Comment on Top service.
   *
   * @var \Drupal\comment_on_top\CommentOnTopService
   */
  protected $commentOnTopService;

  /**
   * Get CID of most liked comment in node.
   *
   * @var \Drupal\comment_on_top_by_likes\CommentOnTopByLikesMostLikedCommentInNode
   */
  protected $mostLikedCommentInNode;

  /**
   * Get current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new CommentOnTopByLikesController instance.
   *
   * @param \Drupal\comment_on_top_by_likes\CommentOnTopByLikesMostLikedCommentInNode $most_liked_comment_in_node
   *   Get CID of most liked comment in node.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Get current route match.
   * @param \Drupal\comment_on_top\CommentOnTopService $comment_on_top_service
   *   The Comment on Top service.
   */
  public function __construct(CommentOnTopByLikesMostLikedCommentInNode $most_liked_comment_in_node, RouteMatchInterface $route_match, CommentOnTopService $comment_on_top_service) {
    $this->mostLikedCommentInNode = $most_liked_comment_in_node;
    $this->routeMatch = $route_match;
    $this->commentOnTopService = $comment_on_top_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('comment_on_top_by_likes.most_liked_comment_in_node'),
      $container->get('current_route_match'),
      $container->get('comment_on_top.service')
    );
  }

  /**
   * Invokes stickOnTop() method which sticks on top most liked comment
   */
  public function stickCommentOnTopByLikes() {
    // Get current node id (nid) from current route
    $node = $this->routeMatch->getParameter('node');
    $node_id = $node->id();

    // Get most liked comment_id with CommentOnTopByLikesMostLikedCommentInNode service
    $comment_id = $this->mostLikedCommentInNode->getMostLikedCommentInNode(intval($node_id));

    // Get method stickOnTop from comment_on_top.service
    $this->commentOnTopService->stickOnTop($node_id, $comment_id);
  }
}
