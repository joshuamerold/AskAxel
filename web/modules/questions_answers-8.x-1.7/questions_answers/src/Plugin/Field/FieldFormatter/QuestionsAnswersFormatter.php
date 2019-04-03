<?php

namespace Drupal\questions_answers\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\questions_answers\Entity\Question;
use Drupal\questions_answers\Form\QuestionForm;
use Drupal\questions_answers\Form\AnswerForm;
use Drupal\questions_answers\Form\ReportQuestionForm;
use Drupal\questions_answers\Form\ReportAnswerForm;
use Drupal\questions_answers\Form\HelpfulForm;
use Drupal\questions_answers\Form\SubscribeForm;

/**
 * Implementation of Questions and Answers data formatter.
 *
 * @FieldFormatter(
 *   id = "questions_answers_formatter",
 *   label = @Translation("Questions and Answers"),
 *   field_types = {
 *     "questions_answers"
 *   }
 * )
 */
class QuestionsAnswersFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal Form Builder service.
   *
   * @var Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * Drupal Form Builder service.
   *
   * @var Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Drupal current user service container.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal mail manager service container.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal entity query service container.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Drupal entity manager service container.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal container.
   *
   * @var Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, FormBuilder $form_builder, DateFormatter $date_formatter, AccountProxyInterface $current_user, ConfigFactory $config_factory, QueryFactory $entity_query, EntityTypeManager $entity_type_manager, ContainerInterface $container) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->formBuilder = $form_builder;
    $this->dateFormatter = $date_formatter;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->entityQuery = $entity_query;
    $this->entityTypeManager = $entity_type_manager;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('form_builder'),
      $container->get('date.formatter'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Auto-approve newly posted questions?
    $elements['default_questions_status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto-approve new questions'),
      '#default_value' => $this->getSetting('default_questions_status'),
      '#description' => $this->t('Submitted questions will automatically be approved. Please note that users with administration permissions will have their questions auto-approved regardless of this setting.'),
    ];
    // Auto-approve newly posted answers?
    $elements['default_answers_status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto-approve new answers'),
      '#default_value' => $this->getSetting('default_answers_status'),
      '#description' => $this->t('Submitted answers will automatically be approved. Please note that users with administration permissions will have their answer auto-approved regardless of this setting.'),
    ];
    // Show "Was this helpful" votes for each answer?
    $elements['allow_helpful'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show "Was this Helpful"'),
      '#default_value' => $this->getSetting('allow_helpful'),
      '#description' => $this->t('This setting allows for the use of "Was this Helpful" forms for each answer. This allows logged-in users to vote on what questions were helpful.'),
    ];
    // Determine which format should be used for date display.
    $elements['date_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Date display format'),
      '#default_value' => $this->getSetting('date_display'),
      '#description' => $this->t('This setting allows for the selection of a date format for displaying posting times. These options can be modified on the @dateLink.', [
        '@dateLink' => Link::createFromRoute('datetime settings page', 'entity.date_format.collection')->toString(),
      ]),
      '#options' => [],
    ];
    foreach (DateFormat::loadMultiple() as $key => $format) {
      $elements['date_display']['#options'][$key] = $format->get('label');
    }
    // Notify these emails of new questions.
    $elements['notify_new_questions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email notifications of new questions'),
      '#default_value' => $this->getSetting('notify_new_questions'),
      '#description' => $this->t('Provide a list of emails here, one per line, for addresses where notifications should be sent when new questions are asked. This can be helpful if the questions are unapproved by default and need to be reviewed by a site administrator.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    // Show which graph we are currently using for the field.
    $summary = [
      $this->t('Displays a questions and answers form.'),
    ];
    $summary[] = $this->t('Auto-approve questions: <strong>@setting</strong>', [
      '@setting' => $this->getSetting('default_questions_status') ? $this->t('Yes') : $this->t('No'),
    ]);
    $summary[] = $this->t('Auto-approve answers: <strong>@setting</strong>', [
      '@setting' => $this->getSetting('default_answers_status') ? $this->t('Yes') : $this->t('No'),
    ]);
    $summary[] = $this->t('Show "Was this Helpful": <strong>@setting</strong>', [
      '@setting' => $this->getSetting('allow_helpful') ? $this->t('Yes') : $this->t('No'),
    ]);
    $summary[] = $this->t('Date display format: <strong>@setting</strong>', [
      '@setting' => DateFormat::load($this->getSetting('date_display'))->get('label'),
    ]);
    $summary[] = $this->t('Email notifications of new questions: <strong>@setting</strong>', [
      '@setting' => $this->getSetting('notify_new_questions'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'default_questions_status' => FALSE,
      'default_answers_status' => FALSE,
      'allow_helpful' => TRUE,
      'date_display' => 'fallback',
      'notify_new_questions' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Get the entity we're attached to.
    $entity = $items->getEntity();
    // Get the field name.
    $fieldname = $this->fieldDefinition->getItemDefinition()->getFieldDefinition()->getName();
    // Get the field cardinality.
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    // Get the module settings.
    $questions_answers_settings = $this->configFactory->get('questions_answers.settings');

    // Loop over each graph and build data.
    $questions = [];

    foreach ($items as $delta => $item) {
      // Load the question.
      $question_ids = $this->entityQuery->get('questions_answers_question')
        ->condition('entity_id', $entity->id(), '=')
        ->condition('bundle', $entity->getEntityTypeId(), '=')
        ->condition('id', $item->question_id, '=')
        ->execute();
      if (count($question_ids) > 0) {
        $question = Question::load(array_pop($question_ids));
      }

      // Only display question if user has access.
      if (isset($question) && $question->checkViewable($this->currentUser)) {

        // Build the display.
        $questions[$delta] = [
          '#theme' => 'questions_answers_question',
          '#question' => $this->t('@title @published @reported', [
            '@title' => $question->getValue(),
            '@published' => ($question->isPublished() == 1 ? '' : $this->t('(Unpublished)')),
            '@reported' => ($this->currentUser->hasPermission('administer questions and answers') && $question->getReportCount() > 0 ? $this->t('(Reported)') : ''),
          ]),
          '#submitted' => $this->t('Asked by %username on @date. @count @persons subscribed to this question.', [
            '%username' => $question->getAuthorName(),
            '@date' => $this->dateFormatter->format($question->getCreatedTime(), $this->getSetting('date_display')),
            '@count' => $question->getSubscribedCount(),
            '@persons' => $question->getSubscribedCount() == 1 ? $this->t('person') : $this->t('people'),
          ]),
          '#report_form' => [],
          '#subscribe_form' => [],
          '#answers' => [],
          '#answer_form' => [],
        ];

        // Load the answers.
        foreach ($question->getAnswers() as $key => $answer) {

          // Only display answer if user has access..
          if ($answer->checkViewable()) {
            // Build the answer.
            $questions[$delta]['#answers'][$key] = [
              '#theme' => 'questions_answers_answer',
              '#username' => $answer->getAuthorName(),
              '#roles' => [],
              '#created' => $this->dateFormatter->format($answer->getCreatedTime(), $this->getSetting('date_display')),
              '#published' => $answer->isPublished() == 1,
              '#answer' => $answer->getValue(),
              '#verified' => $answer->isVerified(),
              '#report_form' => [],
              '#helpful_form' => [],
            ];

            // Build report form if user has permissions.
            if ($this->currentUser->hasPermission('report questions and answers')) {
              // We manually build the form here so we can inject the answer.
              $report_form = ReportAnswerForm::create($this->container);
              $report_form->setAnswer($answer);
              $questions[$delta]['#answers'][$key]['#report_form'] = $this->formBuilder->getForm($report_form);
            }

            // Build helpful form if user is logged in.
            if ($this->getSetting('allow_helpful')) {
              // We manually build the form here so we can inject the answer.
              $helpful_form = HelpfulForm::create($this->container);
              $helpful_form->setAnswer($answer);
              $questions[$delta]['#answers'][$key]['#helpful_form'] = $this->formBuilder->getForm($helpful_form);
            }

            // Add the roles.
            foreach (['qa_staff_member', 'qa_top_contributor'] as $roleId) {
              if ($answer->getOwner()->hasRole($roleId)) {
                $questions[$delta]['#answers'][$key]['#roles'][] = [
                  'id' => Html::getClass($roleId),
                  'name' => $this->entityTypeManager->getStorage('user_role')->load($roleId)->get('label'),
                ];
              }
            }
          }
        }

        // Build report form if user has permissions.
        if ($this->currentUser->hasPermission('report questions and answers')) {
          // We manually build the form here so we can inject the question.
          $report_form = ReportQuestionForm::create($this->container);
          $report_form->setQuestion($question);
          $questions[$delta]['#report_form'] = $this->formBuilder->getForm($report_form);
        }

        // Build subscribe form if user has permissions.
        if ($this->currentUser->hasPermission('subscribe questions and answers')) {
          // We manually build the form here so we can inject the question.
          $subscribe_form = SubscribeForm::create($this->container);
          $subscribe_form->setQuestion($question);
          $questions[$delta]['#subscribe_form'] = $this->formBuilder->getForm($subscribe_form);
        }

        // Build answer form if user has permission to answer questions.
        if ($this->currentUser->hasPermission('answer questions and answers') && $question->isPublished() == 1) {
          // We manually build the form here so we can inject the question.
          $answer_form = AnswerForm::create($this->container);
          $answer_form->setQuestion($question);
          $answer_form->setSettings($this->getSettings());
          $questions[$delta]['#answer_form'] = $this->formBuilder->getForm($answer_form);
        }
      }
      unset($question);
    }

    // Build the terms and conditions link.
    $terms_and_conditions = [];
    if (!empty($questions_answers_settings->get('terms_and_conditions'))) {
      $terms_and_conditions = Link::fromTextAndUrl($this->t('Terms and Conditions'), Url::fromUserInput($questions_answers_settings->get('terms_and_conditions')))->toString();
    }

    // Build the question form if permission.
    $question_form = [];
    if ($this->currentUser->hasPermission('ask questions and answers')) {
      // Check to make sure we haven't hit the field limit.
      if ($cardinality < 0 || count($items) < $cardinality) {
        // We manually build the form here so we can inject the field settings.
        $question_form = QuestionForm::create($this->container);
        $question_form->setEntity($entity);
        $question_form->setFieldName($fieldname);
        $question_form->setSettings($this->getSettings());
        $question_form = $this->formBuilder->getForm($question_form);
      }
    }
    elseif (!empty($questions_answers_settings->get('no_access_message'))) {
      // User has no permission, show the message.
      $question_form = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['no-access-message'],
        ],
        '#children' => $questions_answers_settings->get('no_access_message'),
      ];
    }

    return [
      [
        '#theme' => 'questions_answers',
        '#questions' => $questions,
        '#question_form' => $question_form,
        '#terms_and_conditions' => $terms_and_conditions,
      ],
    ];
  }

}
