<?php declare(strict_types = 1);

  namespace Drupal\comment_on_top_by_likes\Form;

  use Drupal\Core\Form\ConfigFormBase;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Entity\EntityTypeManagerInterface;
  use Symfony\Component\DependencyInjection\ContainerInterface;
  use Drupal\Core\Ajax\AjaxResponse;
  use Drupal\Core\Ajax\HtmlCommand;
  use Drupal\comment_on_top\CommentOnTopByLikesPerNode;


  /**
   * Configure Comment on top by likes settings for this site.
   */
  final class ContentTypeCommentOnTopByLikesSettingsForm extends ConfigFormBase {


    /**
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;


    /**
     * The CommentOnTopByLikesPerNode service.
     *
     * @var \Drupal\comment_on_top\CommentOnTopByLikesPerNode
     */
    protected $commentOnTopByLikesPerNode;


    /**
     * Constructs a new ContentTypeCommentOnTopByLikesSettingsForm object.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager.
     * @param \Drupal\comment_on_top\CommentOnTopByLikesPerNode $comment_on_top_by_likes_per_node
     *   The CommentOnTopByLikesPerNode service.
     */
    public function __construct(EntityTypeManagerInterface $entity_type_manager,
                                CommentOnTopByLikesPerNode $comment_on_top_by_likes_per_node) {
      $this->entityTypeManager = $entity_type_manager;
      $this->commentOnTopByLikesPerNode = $comment_on_top_by_likes_per_node;
    }


    /**
     * {@inheritdoc}
     */
    public function getFormId(): string {
      return 'comment_on_top_by_likes_content_type_comment_on_top_by_likes_settings';
    }


    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames(): array {
      return ['comment_on_top_by_likes.settings'];
    }


    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('entity_type.manager'),
        $container->get('comment_on_top_by_likes.node')
      );
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state): array {
      $config = $this->config('comment_on_top_by_likes.settings');

      // Fetch all content types
      $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();

      foreach ($contentTypes as $contentType) {

        $form['label'] = ['#markup' => '<strong>'.t('Please select the content type settings as follows: </strong><br>
        - The MOST LIKED comment will be pinned to the top. <br>
        - Use the DEFAULT DRUPAL COMMENT order. This allows for manual sticking of FEATURED COMMENTS to the top.').
          '<br><br>'.
        '<em>'.'Note: Every individually node you checked/unchecked Stick on top by likes checkbox will be changed!'.'</em>'];

        $form[] = [
          '#markup' => '<hr>'
        ];

        //Get all content types as checkboxes
        $form[$contentType->id()] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable for @type', ['@type' => $contentType->label()]),
          '#default_value' => $config->get($contentType->id()) !== NULL ? $config->get($contentType->id()) : 0,
          '#ajax' => [
            'callback' => '::toggleCommentOrderVisibility',
            'wrapper' => $contentType->id() . '_comment_order_wrapper',
          ],
        ];

        //Form element with radio buttons. Hidden if content type is not checked.
        $form[$contentType->id().'_comment_order_wrapper'] = [
          '#type' => 'container',
          '#attributes' => ['id' => $contentType->id() . '_comment_order_wrapper'],
        ];

        $form[$contentType->id().'_comment_order_wrapper'][$contentType->id().'_comment_order'] = [
          '#type' => 'radios',
          '#title' => $this->t('Comment Order'),
          '#options' => [
            $contentType->id().'_most_liked' => $this->t('Most liked comment on top'),
            $contentType->id().'_default' => $this->t('Default Drupal comments order'),
          ],
          '#states' => [
            'invisible' => [
              ':input[name="' . $contentType->id() . '"]' => ['checked' => FALSE],
            ],
          ],
          '#default_value' => $config->get($contentType->id().'_comment_order') === null
            ? $contentType->id().'_default' : $config->get($contentType->id().'_comment_order'),
        ];
      }
      return parent::buildForm($form, $form_state);
    }

    //Ajax method of radio buttons
    public function toggleCommentOrderVisibility(array &$form, FormStateInterface $form_state): AjaxResponse {
      $response = new AjaxResponse();

      foreach ($form as $key => $element) {
        if (is_string($key) && strpos($key, '_comment_order') !== FALSE && isset($element['#ajax'])) {

          // Get checkbox
          $checkbox_name = str_replace('_comment_order', '', $key);

          // Check is checkbox checked
          $checkbox_value = $form_state->getValue($checkbox_name);

          // If checkbox is not checked, hide radio buttons.
          if (empty($checkbox_value)) {
            $response->addCommand(new HtmlCommand('#' . $checkbox_name . '_comment_order_wrapper', ''));
          }
          // If checkbox is checked, dispaly radio buttons.
          else {
            $response->addCommand(new HtmlCommand('#' . $checkbox_name . '_comment_order_wrapper', $element));
          }
        }
      }
      return $response;
    }


    /**
     * {@inheritdoc}
     * Checking is any content type selected. If it's not, returns error.
     */
    public function validateForm(array &$form, FormStateInterface $form_state): void {
      parent::validateForm($form, $form_state);

      $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
      $contentTypeCheckboxes = [];

      foreach ($contentTypes as $contentType) {
        $contentTypeCheckbox = $form_state->getValue($contentType->id());
        $contentTypeCheckboxes[] = $contentTypeCheckbox;
      }

      if (array_sum($contentTypeCheckboxes) === 0) {
        $form_state->setErrorByName('',
          $this->t('Please enable at least one content type option to save configuration.'));
      }
    }


    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state): void {
      $config = $this->config('comment_on_top_by_likes.settings');

      //Setting values in configuration for updating db table
      foreach ($form_state->getValues() as $key => $value) {
        if (strpos($key, '_comment_order') !== false) {
          $config->set($key, $value);

          $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
          foreach ($contentTypes as $contentType) {

            //Updating db only if content type is checked
            if  ($form_state->getValue($contentType->id()) === 1) {
              if ($value == $contentType->id() . '_most_liked') {
                $type = str_replace('_comment_order', '', $key);
                $this->commentOnTopByLikesPerNode->updateStickedOnTopByLikesByType($type, 1);
              } elseif ($value == $contentType->id() . '_default') {
                $type = str_replace('_comment_order', '', $key);
                $this->commentOnTopByLikesPerNode->updateStickedOnTopByLikesByType($type, 0);
              }
            }
          }
        } elseif ($key !== 'op') {
          $config->set($key, $value)->save();
        }
      }

      $form_state->setRedirect('comment_on_top_by_likes.content_type_comment_by_likes_confirm');
    }
  }
