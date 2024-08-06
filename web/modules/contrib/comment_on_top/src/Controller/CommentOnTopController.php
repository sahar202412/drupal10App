<?php

  namespace Drupal\comment_on_top\Controller;

  use Drupal\Core\Controller\ControllerBase;
  use Drupal\Core\Url;
  use Symfony\Component\HttpFoundation\RedirectResponse;
  use Drupal\comment_on_top\CommentOnTopService;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * Controller for managing comments and their stickiness.
   */
  class CommentOnTopController extends ControllerBase {

    /**
     * The Comment on Top service.
     *
     * @var \Drupal\comment_on_top\CommentOnTopService
     */
    protected $commentOnTopService;

    /**
     * Constructs a new CommentOnTopController object.
     *
     * @param \Drupal\comment_on_top\CommentOnTopService $comment_on_top_service
     *   The Comment on Top service.
     */
    public function __construct(CommentOnTopService $comment_on_top_service) {
      $this->commentOnTopService = $comment_on_top_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('comment_on_top.service')
      );
    }

    /**
     * Stick a comment on top.
     *
     * @param int $node_id
     *   The node ID.
     * @param int $comment_id
     *   The comment ID.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *   A redirect response.
     */
    public function stickOnTop($node_id, $comment_id) {
      $this->commentOnTopService->stickOnTop($node_id, $comment_id);

      $url = Url::fromRoute('entity.node.canonical', ['node' => $node_id]);
      return new RedirectResponse($url->toString());
    }

    /**
     * Remove a comment from being on top.
     *
     * @param int $node_id
     *   The node ID.
     * @param int $comment_id
     *   The comment ID.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *   A redirect response.
     */
    public function removeFromTop($node_id, $comment_id) {
      $this->commentOnTopService->removeFromTop($node_id, $comment_id);

      $url = Url::fromRoute('entity.node.canonical', ['node' => $node_id]);
      return new RedirectResponse($url->toString());
    }
  }
