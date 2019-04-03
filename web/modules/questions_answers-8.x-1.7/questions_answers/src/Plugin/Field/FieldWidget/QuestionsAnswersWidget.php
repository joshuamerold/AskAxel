<?php

namespace Drupal\questions_answers\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Utility\Token;
use Drupal\questions_answers\Entity\Question;

/**
 * Plugin implementation of the 'questions_answers' widget.
 *
 * @FieldWidget(
 *   id = "questions_answers_widget",
 *   module = "questions_answers",
 *   label = @Translation("Questions and Answers"),
 *   field_types = {
 *     "questions_answers"
 *   }
 * )
 */
class QuestionsAnswersWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal Form Builder service.
   *
   * @var Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Drupal entity query service container.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Drupal entity type manager service container.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal token service.
   *
   * @var Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, DateFormatter $date_formatter, QueryFactory $entity_query, EntityTypeManager $entity_type_manager, Token $token) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->dateFormatter = $date_formatter;
    $this->entityQuery = $entity_query;
    $this->entityTypeManager = $entity_type_manager;
    $this->token = $token;
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
      $configuration['third_party_settings'],
      $container->get('date.formatter'),
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Set the data for this graph.
    $this->fieldName = $this->fieldDefinition->getName();

    if (!isset($items[$delta]->question_id)) {
      return [];
    }

    // Load the question.
    $question = Question::load($items[$delta]->question_id);

    if (!isset($question)) {
      return [];
    }

    $element['user'] = [
      '#markup' => $this->t('Submitted by @user on @time. @count subscribed', [
        '@user' => $question->getOwner()->toLink()->toString(),
        '@time' => $this->dateFormatter->format($question->getCreatedTime()),
        '@count' => count($question->getSubscribed()),
      ]),
    ];

    // Unmodified fields.
    $element['question_id'] = [
      '#type' => 'hidden',
      '#value' => $items[$delta]->question_id,
    ];

    // Actual question text.
    $element['question'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Question'),
      '#size' => 50,
      '#default_value' => $question->getValue(),
      '#description' => $this->t('Leaving this field empty will delete this question.'),
    ];

    // Question status.
    $element['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Published?'),
      '#default_value' => $question->isPublished(),
    ];

    // Reports status.
    $num_reports = count($question->getReports());
    if ($num_reports > 0) {
      $element['clear_reports'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Clear Reports? Question has been reported by @count user(s)', [
          '@count' => $num_reports,
        ]),
        '#default_value' => FALSE,
      ];
    }

    // Load the answers.
    foreach ($question->getAnswers() as $key => $answer) {
      $element['answers'][$key] = [
        '#type' => 'fieldset',
        '#open' => TRUE,
        '#title' => $this->t('Answer'),
        'user' => [
          '#markup' => $this->t('Submitted by @user on @time', [
            '@user' => $answer->getOwner()->toLink()->toString(),
            '@time' => $this->dateFormatter->format($answer->getCreatedTime()),
          ]),
        ],
        'answer' => [
          '#type' => 'textarea',
          '#title' => $this->t('Answer'),
          '#size' => 50,
          '#default_value' => $answer->getValue(),
          '#description' => $this->t('Leaving this field empty will delete this answer.'),
        ],
        'status' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Approved?'),
          '#default_value' => $answer->isPublished(),
        ],
        'verified' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Verified?'),
          '#default_value' => $answer->isVerified(),
        ],
        'answer_id' => [
          '#type' => 'hidden',
          '#value' => $answer->id(),
        ],
      ];
      $num_reports = count($answer->getReports());
      if ($num_reports > 0) {
        $element['answers'][$key]['clear_reports'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Clear Reports? Answer has been reported by @count user(s)', [
            '@count' => $num_reports,
          ]),
          '#default_value' => FALSE,
        ];
      }
    }

    $form_state->setCached(FALSE);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => &$item) {
      // Load the question.
      $question = $this->entityTypeManager->getStorage('questions_answers_question')->load($item['question_id']);
      if (isset($question)) {
        // If we are deleting, wipe this out.
        if (empty($item['question'])) {
          // Delete all associated answers.
          foreach ($question->getAnswers() as $answer) {
            $answer->delete();
          }
          // Delete the question.
          $question->delete();
          // Set to blank to delete the field entry.
          unset($values[$key]);
        }
        else {
          $question->setValue($item['question']);
          $question->setPublished($item['status']);
          // If we are clearing answer reports.
          if (isset($item['clear_reports']) && $item['clear_reports'] == 1) {
            $question->clearReports();
          }

          // Update the answers.
          if (isset($item['answers'])) {
            foreach ($item['answers'] as $answer_values) {
              // Load the answer.
              $answer = $this->entityTypeManager->getStorage('questions_answers_answer')->load($answer_values['answer_id']);
              if (isset($answer)) {
                // If we are deleting, wipe this out.
                if (empty($answer_values['answer'])) {
                  $answer->delete();
                }
                else {
                  $answer->setValue($answer_values['answer']);
                  $answer->setPublished($answer_values['status']);
                  $answer->setVerified($answer_values['verified']);
                  // If we are clearing answer reports.
                  if (isset($answer_values['clear_reports']) && $answer_values['clear_reports'] == 1) {
                    $answer->clearReports();
                  }
                }
              }
            }
            // Update the answer count.
            $question->updateAnswerCount();
            // Remove answers since they aren't part of the question field.
            unset($item['answers']);
          }

          $item = [
            'question_id' => $question->id(),
          ];
        }
      }
      else {
        // The question doesn't exist.
        unset($values[$key]);
      }
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();

    // Determine the number of widgets to display.
    switch ($cardinality) {
      case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED:
        $is_multiple = TRUE;
        break;

      default:
        $is_multiple = ($cardinality > 1);
        break;
    }
    // We cap the max at the number of questions as they are not created here.
    $max = count($items) - 1;

    $title = $this->fieldDefinition->getLabel();
    $description = FieldFilteredMarkup::create($this->token->replace($this->fieldDefinition->getDescription()));

    $elements = [];

    for ($delta = 0; $delta <= $max; $delta++) {
      // Add a new empty item if it doesn't exist yet at this delta.
      if (!isset($items[$delta])) {
        $items->appendItem();
      }

      // For multiple fields, title and description are handled by the wrapping
      // table.
      if ($is_multiple) {
        $element = [
          '#title' => $this->t('@title (value @number)', ['@title' => $title, '@number' => $delta + 1]),
          '#title_display' => 'invisible',
          '#description' => '',
        ];
      }
      else {
        $element = [
          '#title' => $title,
          '#title_display' => 'before',
          '#description' => $description,
        ];
      }

      $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);

      if ($element) {
        // Input field for the delta (drag-n-drop reordering).
        if ($is_multiple) {
          // We name the element '_weight' to avoid clashing with elements
          // defined by widget.
          $element['_weight'] = [
            '#type' => 'weight',
            '#title' => $this->t('Weight for row @number', ['@number' => $delta + 1]),
            '#title_display' => 'invisible',
            // Note: this 'delta' is the FAPI #type 'weight' element's property.
            '#delta' => $max,
            '#default_value' => $items[$delta]->_weight ?: $delta,
            '#weight' => 100,
          ];
        }

        $elements[$delta] = $element;
      }
    }

    if ($elements) {
      $elements += [
        '#theme' => 'field_multiple_value_form',
        '#field_name' => $field_name,
        '#cardinality' => $cardinality,
        '#cardinality_multiple' => $this->fieldDefinition->getFieldStorageDefinition()->isMultiple(),
        '#required' => $this->fieldDefinition->isRequired(),
        '#title' => $title,
        '#description' => $description,
        '#max_delta' => $max,
      ];
    }

    return $elements;
  }

}
