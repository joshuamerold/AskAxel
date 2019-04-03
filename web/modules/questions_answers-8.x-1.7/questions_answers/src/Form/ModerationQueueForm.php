<?php

namespace Drupal\questions_answers\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\questions_answers\Entity\Question;
use Drupal\questions_answers\Entity\Answer;

/**
 * Form for reporting an answer about an entity question.
 */
class ModerationQueueForm extends FormBase {

  /**
   * Drupal entity query service container.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Drupal Form Builder service.
   *
   * @var Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueryFactory $entity_query, DateFormatter $date_formatter) {
    $this->entityQuery = $entity_query;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'questions_answers_moderation_queue';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#options' => [
        'approve' => $this->t('Approve'),
        'delete' => $this->t('Delete'),
      ],
    ];
    $form['queue'] = [
      '#type' => 'tableselect',
      '#header' => [
        $this->t('Status'),
        $this->t('Type'),
        $this->t('Value'),
        $this->t('In Response to'),
        $this->t('Associated Entity'),
        $this->t('Date'),
      ],
      '#required' => TRUE,
      '#options' => [],
      '#empty' => $this->t('Moderation queue is clear! Nothing to report!'),
    ];

    // Load the unapproved/reported questions.
    $query = $this->entityQuery->get('questions_answers_question');
    $orCondition = $query->orConditionGroup()
      ->condition('status', 0, '=')
      ->condition('reported_count', 0, '>');
    $question_ids = $query
      ->condition($orCondition)
      ->execute();
    foreach (Question::loadMultiple($question_ids) as $question) {
      $form['queue']['#options']['question_' . $question->id()] = [
        ($question->isPublished() == '0' ? $this->t('Unapproved') : $this->t('Reported')),
        $this->t('Question'),
        $question->getValue(),
        '',
        $question->getEntity()->toLink()->toString(),
        $this->dateFormatter->format($question->getCreatedTime()),
      ];
    }

    // Load the unapproved answers.
    $query = $this->entityQuery->get('questions_answers_answer');
    $orCondition = $query->orConditionGroup()
      ->condition('status', 0, '=')
      ->condition('reported_count', 0, '>');
    $answer_ids = $query
      ->condition($orCondition)
      ->execute();
    foreach (Answer::loadMultiple($answer_ids) as $answer) {
      $form['queue']['#options']['answer_' . $answer->id()] = [
        ($answer->isPublished() == '0' ? $this->t('Unapproved') : $this->t('Reported')),
        $this->t('Answer'),
        $answer->getValue(),
        $answer->getQuestion()->getValue(),
        $answer->getEntity()->toLink()->toString(),
        $this->dateFormatter->format($answer->getCreatedTime()),
      ];
    }

    // Only display if there is something in the queue.
    if (count($form['queue']['#options']) > 0) {
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#button_type' => 'primary',
      ];
    }

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
    // Get submitted values.
    $values = $form_state->getValues();

    // Loop over table.
    $countChanges = 0;
    foreach ($values['queue'] as $value) {
      if ($value != '0') {
        $countChanges++;
        // Get the values.
        $parts = explode('_', $value);
        $type = $parts[0];
        $entity_id = $parts[1];
        // Load the id.
        $id = $this->entityQuery->get('questions_answers_' . $type)
          ->condition('id', $entity_id, '=')
          ->execute();
        if (count($id) > 0) {
          // Get the id from the returned array.
          $id = array_pop($id);
          // Load the entity.
          $qaEntity = ($type == 'answer' ? Answer::load($id) : Question::load($id));
          // Perform the action.
          if ($values['action'] == 'approve') {
            // Clear out any reports.
            $qaEntity->clearReports();
            // Set to approved.
            $qaEntity->setPublished(TRUE);
          }
          elseif ($values['action'] == 'delete') {
            // For questions, we need to delete the answers and field as well.
            if ($type == 'question') {
              // Loop over answers and delete them.
              foreach ($qaEntity->getAnswers() as $answer) {
                // Delete the answer.
                $answer->delete();
              }

              // Load the parent entity.
              $entity = $qaEntity->getEntity();
              // Find the fields which are questions so we can check them.
              foreach ($entity as $key => $value) {
                if ($value->getFieldDefinition()->getType() == 'questions_answers') {
                  foreach ($entity->$key as &$field) {
                    if ($field->getString() == $qaEntity->id()) {
                      // If this is the field, unset the linked ID.
                      $field->question_id = '';
                      break;
                    }
                  }
                }
              }
              // Save the entity.
              $entity->save();
            }
            elseif ($type == 'answer') {
              $question = $qaEntity->getQuestion();
            }

            // Delete the question/answer.
            $qaEntity->delete();

            if ($type == 'answer') {
              // Update the answer count.
              $question->updateAnswerCount();
            }
          }
        }
      }
    }

    // Show message.
    $this->messenger()->addStatus($this->t('Successfully @typed @count Questions and Answers values.', [
      '@count' => $countChanges,
      '@type' => $values['action'],
    ]));
  }

}
