<?php declare(strict_types = 1);

namespace Drupal\comment_on_top_by_likes\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;


/**
 * @confirmation form for comment_on_top_by_likes_content_type_comment_on_top_by_likes_settings form.
 */
final class ContentTypeCommentByLikesConfirmForm extends ConfirmFormBase {


  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Constructs a new ContentTypeCommentOnTopByLikesSettingsForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'comment_on_top_by_likes_content_type_comment_by_likes_confirm';
  }


  /**
   * {@inheritdoc}
   */
  public function getQuestion(): TranslatableMarkup {
    $config = $this->config('comment_on_top_by_likes.settings');
    $activeContentTypes = [];

    foreach ($this->entityTypeManager->getStorage('node_type')->loadMultiple() as $contentType) {
      if ($config->getRawData()[$contentType->id()] != 0) {
        $activeContentTypes[] = strtoupper($contentType->id());
      }
    }

    if (!empty($activeContentTypes)) {
      return $this->t('Are you sure you want to change the comments order for all nodes in %content_types content types?', [
        '%content_types' => implode(', ', $activeContentTypes),
      ]);
    }
  }


  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return new Url('comment_on_top_by_likes.content_type_comment_on_top_by_likes_settings');
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('Done!'));
    $form_state->
    setRedirectUrl(new Url('comment_on_top_by_likes.content_type_comment_on_top_by_likes_settings'));
  }
}
