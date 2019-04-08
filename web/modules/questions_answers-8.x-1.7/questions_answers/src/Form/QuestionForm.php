<?php

namespace Drupal\questions_answers\Form;

use Drupal\questions_answers\Entity\Question;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Entity;


/**
 * Form for asking a question about an entity.
 */
class QuestionForm extends FormBase {

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
   * Drupal mail manager service container.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

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
  protected $tokenService;

  /**
   * The entity the questions and answers field is attached to.
   *
   * @var Drupal\Core\Entity\Entity
   */
  protected $entity;

  /**
   * The name of the Q&A field.
   *
   * @var string
   */
  protected $fieldName;
  /**
   * The settings for this field.
   *
   * @var array
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountProxyInterface $current_user, MailManager $mail_manager, ConfigFactory $config_factory, Renderer $renderer_service, Token $token_service) {
    $this->currentUser = $current_user;
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
    $this->rendererService = $renderer_service;
    $this->tokenService = $token_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('plugin.manager.mail'),
      $container->get('config.factory'),
      $container->get('renderer'),
      $container->get('token')
    );
  }

  /**
   * Sets the field settings being used to build this form.
   *
   * @param array $settings
   *   The current field settings..
   */
  public function setSettings(array $settings) {
    $this->settings = $settings;
  }

  /**
   * Sets the field name being used to build this form.
   *
   * @param string $fieldname
   *   The name of the field we are adding this question to.
   */
  public function setFieldName($fieldname) {
    $this->fieldName = $fieldname;
  }

  /**
   * Sets the entity being used to build this form.
   *
   * @param Drupal\Core\Entity\Entity $entity
   *   The entity we are creating a question for.
   */
  public function setEntity(Entity $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    if (!isset($this->entity)) {
      throw new \Exception('Entity not found. setEntity() should be called before form instantiation.');
    }
    if (!isset($this->fieldName)) {
      throw new \Exception('Field Name not found. setFieldName() should be called before form instantiation.');
    }
    if (!isset($this->settings)) {
      throw new \Exception('settings not found. setSettings() should be called before form instantiation.');
    }
    return 'questions_answers_question_form_' . $this->entity->id() . '_' . $this->fieldName;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;
    $form['info'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['header-info'],
      ],
    ];
    $form['question'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Was möchtest du wissen?'),
      '#size' => 50,
      '#required' => TRUE,
      '#default_value' => '',
    ];
    // Allow anonymous users to subscribe to their own question.
    if ($this->currentUser->isAnonymous()) {
      $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email Addresse'),
        '#description' => $this->t('Optional. Enter if you would like to receive an email when your question is answered.'),
        '#required' => FALSE,
        '#default_value' => '',
        '#states' => [
          'visible' => [
            ':input[name="question"]' => ['filled' => TRUE],
          ],
        ],
      ];
    }
    else {
      $form['email-info'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['email-info'],
        ],
        '#children' => $this->t('You will receive an email at @email when this question is answered.', [
          '@email' => $this->currentUser->getEmail(),
        ]),
        '#states' => [
          'visible' => [
            ':input[name="question"]' => ['filled' => TRUE],
          ],
        ],
      ];
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Senden!'),
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

    // Auto-subscribe the person asking the question.
    $subscribed = [];
    if (!$this->currentUser->isAnonymous() && $this->currentUser->hasPermission('subscribe questions and answers')) {
      $subscribed = [$this->currentUser->id()];
    }

    // Create the question entity.
    $question = Question::create([
      'entity_id' => $this->entity->id(),
      'bundle' => $this->entity->getEntityTypeId(),
      'question' => $values['question'],
      'subscribed' => serialize($subscribed),
      'subscribed_count' => count($subscribed),
      'status' => (($this->settings['default_questions_status'] || $this->currentUser->hasPermission('administer questions and answers')) ? 1 : 0),
      'uid' => $this->currentUser->id(),
    ]);
    $question->save();

    if (!empty($form_state->getValue('email'))) {
      // Subscribe.
      $question->addSubscription($form_state->getValue('email'));
    }

    // Link the questin to the field.
    $fieldname = $this->fieldName;
    $this->entity->$fieldname->appendItem([
      'question_id' => $question->id(),
    ]);
    // Save the entity.
    $this->entity->save();

    // Show message.
    $this->messenger()->addStatus($this->t('Frage erfolgreich übermittelt!'));

    // Send notifications that a new question has been submitted.
    foreach (explode(PHP_EOL, $this->settings['notify_new_questions']) as $email) {
      if (!empty($email)) {
        // Build the email.
        $subjectText = $this->t('Neue Frage auf [site:name]');
        $emailTemplate = [
          '#theme' => 'questions_answers_email_new_question',
        ];
        $emailText = $this->rendererService->render($emailTemplate);

        $tokenData = [
          $this->entity->getEntityTypeId() => $this->entity,
          'entity' => $this->entity,
          'question' => $question,
        ];
        $params['subject'] = $this->tokenService->replace($subjectText, $tokenData);
        $params['message'] = $this->tokenService->replace($emailText, $tokenData);

        // Send the email.
        $this->mailManager->mail('questions_answers', 'notify_subscriber', $email, $this->currentUser()->getPreferredLangcode(), $params, NULL, TRUE);
      }
    }
  }

}
