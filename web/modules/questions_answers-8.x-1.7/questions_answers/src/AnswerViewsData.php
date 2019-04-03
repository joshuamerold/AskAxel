<?php

namespace Drupal\questions_answers;

use Drupal\views\EntityViewsData;

/**
 * Provides views data for the answer entity type.
 */
class AnswerViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Set table group.
    $data['questions_answers_answer']['table']['group'] = $this->t('Answer');
    $data['questions_answers_answer']['table']['base'] = [
      'field' => 'id',
      'title' => t('Questions and Answers Answer'),
      'help' => t('Answer entities for Questions and Answers.'),
      'weight' => -10,
    ];
    $data['questions_answers_answer']['table']['provider'] = 'questions_answers_answer';

    // Answer text.
    $data['questions_answers_answer']['answer'] = [
      'title' => $this->t('Answer'),
      'help' => $this->t('The text of the answer being asked.'),
      'field' => [
        'id' => 'standard',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'argument' => [
        'id' => 'string',
      ],
    ];

    // Reported count.
    $data['questions_answers_answer']['reported'] = [
      'title' => $this->t('Reported Users'),
      'help' => $this->t('The users reporting this answer.'),
      'field' => [
        'id' => 'questions_answers_reported',
      ],
    ];
    $data['questions_answers_answer']['reported_count'] = [
      'title' => $this->t('Reported Count'),
      'help' => $this->t('The reports count for this question.'),
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
    ];

    // Helpful votes.
    $data['questions_answers_answer']['helpful'] = [
      'title' => $this->t('Helpful Votes'),
      'help' => $this->t('The reports count for this answer.'),
      'field' => [
        'id' => 'questions_answers_helpfulvotes',
      ],
    ];

    // Entity.
    $data['questions_answers_answer']['entity_id'] = [
      'title' => $this->t('Entity'),
      'help' => $this->t('The entity to which this question is associated.'),
      'field' => [
        'id' => 'questions_answers_parententity',
      ],
    ];

    // User ID.
    $data['questions_answers_answer']['uid'] = [
      'title' => $this->t('User ID'),
      'help' => $this->t('ID of the author of this answer. If you need more information than the ID, add the Author relationship.'),
      // FIXME - can we format this better?
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
    ];

    // Author.
    $data['questions_answers_answer']['author_name'] = [
      'title' => $this->t('Author Name'),
      'help' => $this->t('Name of the author of this answer. If you need more information than the name, add the Author relationship.'),
      'field' => [
        'id' => 'questions_answers_author',
      ],
    ];

    // Verified status.
    $data['questions_answers_answer']['verified'] = [
      'title' => $this->t('Verified'),
      'help' => $this->t('The current verified status of the answer.'),
      'field' => [
        'id' => 'boolean',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'boolean',
        'label' => $this->t('Verified'),
        'type' => 'yes-no',
        'use_equal' => TRUE,
      ],
    ];

    // Approval status.
    $data['questions_answers_answer']['status'] = [
      'title' => $this->t('Status'),
      'help' => $this->t('The current approval status of the answer.'),
      'field' => [
        'id' => 'boolean',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'boolean',
        'label' => $this->t('Published'),
        'type' => 'yes-no',
        'use_equal' => TRUE,
      ],
    ];

    // Date created.
    $data['questions_answers_answer']['created'] = [
      'title' => $this->t('Created'),
      'help' => $this->t('The date the answer was submitted.'),
      'field' => [
        'id' => 'date',
      ],
      'sort' => [
        'id' => 'date',
      ],
      'filter' => [
        'id' => 'date',
      ],
    ];

    // Remove views that don't make sense.
    unset($data['questions_answers_answer']['rendered_entity']);
    unset($data['questions_answers_answer']['operations']);

    // Relationship to Author User table.
    $data['questions_answers_answer']['user_id'] = [
      'title' => $this->t('Answer Author'),
      'help' => $this->t('The author of this answer'),
      'relationship' => [
        'title' => $this->t('Answer Author'),
        'base' => 'users',
        'relationship field' => 'uid',
        'base field' => 'uid',
        'id' => 'standard',
        'handler' => 'views_handler_relationship',
        'label' => $this->t('Answer Author'),
      ],
    ];

    // Relationship to Question table.
    $data['questions_answers_answer']['question_id'] = [
      'title' => $this->t('Question'),
      'help' => $this->t('The question this answer refers to'),
      'relationship' => [
        'title' => $this->t('Question'),
        'base' => 'questions_answers_question',
        'relationship field' => 'entity_id',
        'base field' => 'id',
        'id' => 'standard',
        'handler' => 'views_handler_relationship',
        'label' => $this->t('Question'),
      ],
    ];

    return $data;
  }

}
