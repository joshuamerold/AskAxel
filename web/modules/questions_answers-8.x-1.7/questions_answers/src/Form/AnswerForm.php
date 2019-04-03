<?php

namespace Drupal\questions_answers\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Utility\Token;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\questions_answers\Entity\Question;
use Drupal\questions_answers\Entity\Answer;

/**
 * Form for answering a question about an entity.
 */
class AnswerForm extends FormBase {

  /**
   * Drupal current user service container.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal mail manager service container.
   *
   * @var Drupal\Core\Mail\MailManager
   */
  protected $mailManager;

  /**
   * Drupal renderer service.
   *
   * @var Drupal\Core\Render\Renderer
   */
  protected $rendererService;

  /**
   * Drupal token service.
   *
   * @var Drupal\Core\Utility\Token
   */
  protected $languageManager;

  /**
   * Drupal token service.
   *
   * @var Drupal\Core\Language\LanguageManagerInterface
   */
  protected $tokenService;

  /**
   * Question being answered.
   *
   * @var Drupal\questions_answers\Entity\Question
   */
  protected $question;

  /**
   * The settings for this field.
   *
   * @var array
   */
  protected $settings;

  /**
   * Drupal entity manager service container.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountProxyInterface $current_user, MailManager $mail_manager, Renderer $renderer_service, Token $token_service, LanguageManagerInterface $language_manager, EntityTypeManager $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->mailManager = $mail_manager;
    $this->rendererService = $renderer_service;
    $this->tokenService = $token_service;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('plugin.manager.mail'),
      $container->get('renderer'),
      $container->get('token'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Sets the question being used to build this form.
   *
   * @param Drupal\questions_answers\Entity\Question $question
   *   The question we are building the form for.
   */
  public function setQuestion(Question $question) {
    $this->question = $question;
  }

  /**
   * Sets the question being used to build this form.
   *
   * @param array $settings
   *   The field settings for this questions and answers field.
   */
  public function setSettings(array $settings) {
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    if (!isset($this->question)) {
      throw new \Exception('Question not found. setQuestion() should be called before form instantiation.');
    }
    if (!isset($this->settings)) {
      throw new \Exception('settings not found. setSettings() should be called before form instantiation.');
    }
    return 'questions_answers_answer_form_' . $this->question->id();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;
    $form['answer'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Answer this question'),
      '#size' => 50,
      '#required' => TRUE,
      '#default_value' => '',
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the submitted values.
    $values = $form_state->getValues();

    // Load the associated entity.
    $entity = $this->question->getEntity();

    // Get the current user account.
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());

    // Create the answer entity.
    $answer = Answer::create([
      'entity_id' => $this->question->id(),
      'answer' => $values['answer'],
      'uid' => $this->currentUser->id(),
      'status' => (($this->settings['default_answers_status'] || $this->currentUser->hasPermission('administer questions and answers')) ? 1 : 0),
      'verified' => ($user->hasRole('qa_staff_member') ? 1 : 0),
    ]);
    $answer->save();

    // TODO - move this to cron?
    foreach (array_unique($this->question->getSubscribed()) as $uid) {
      // Use stored value as email for anonymous.
      $email = $uid;
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      // Load the subscribed user.
      if (is_numeric($uid)) {
        $subscribedUser = $this->entityTypeManager->getStorage('user')->load($uid);
        $email = $subscribedUser->getEmail();
        $langcode = $subscribedUser->getPreferredLangcode();
      }

      // Build the email.
      $subjectText = $this->t('One of your questions has been answered at [site:name]');
      $emailTemplate = [
        '#theme' => 'questions_answers_email_subscription',
      ];
      $emailText = $this->rendererService->render($emailTemplate);

      $tokenData = [
        $entity->getEntityTypeId() => $entity,
        'entity' => $entity,
        'question' => $this->question,
        'answer' => $answer,
      ];
      $params['subject'] = $this->tokenService->replace($subjectText, $tokenData);
      $params['message'] = $this->tokenService->replace($emailText, $tokenData);
      // Send the email.
      $this->mailManager->mail('questions_answers', 'notify_subscriber', $email, $langcode, $params, NULL, TRUE);
    }

    // Update the answer count.
    $this->question->updateAnswerCount();

    // Show message.
    $this->messenger()->addStatus($this->t('Your answer has been successfully submitted.'));
  }

}
