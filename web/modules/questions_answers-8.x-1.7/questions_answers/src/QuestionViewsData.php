<?php

namespace Drupal\questions_answers;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\views\EntityViewsData;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFieldManager;

/**
 * Provides views data for the question entity type.
 */
class QuestionViewsData extends EntityViewsData {

  /**
   * Drupal entity field manager service container.
   *
   * @var Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, SqlEntityStorageInterface $storage_controller, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, TranslationInterface $translation_manager, EntityFieldManager $entity_field_manager) {
    $this->entityType = $entity_type;
    $this->entityManager = $entity_manager;
    $this->storage = $storage_controller;
    $this->moduleHandler = $module_handler;
    $this
      ->setStringTranslation($translation_manager);
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static($entity_type, $container
      ->get('entity.manager')
      ->getStorage($entity_type
      ->id()), $container
      ->get('entity.manager'), $container
      ->get('module_handler'), $container
      ->get('string_translation'), $container
      ->get('entity_field.manager'), $container
      ->get('typed_data_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Set table group.
    $data['questions_answers_question']['table']['group'] = $this->t('Question');
    $data['questions_answers_question']['table']['base'] = [
      'field' => 'id',
      'title' => t('Questions and Answers Question'),
      'help' => t('Question entities for Questions and Answers.'),
      'weight' => -10,
    ];
    $data['questions_answers_question']['table']['provider'] = 'questions_answers_question';

    // Question text.
    $data['questions_answers_question']['question'] = [
      'title' => $this->t('Question'),
      'help' => $this->t('The text of the question being asked.'),
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

    // Answers text.
    $data['questions_answers_question']['answers_list'] = [
      'title' => $this->t('Answers List'),
      'help' => $this->t('List of answers for this question. IF you need more info, use the Answers relationship.'),
      'field' => [
        'id' => 'questions_answers_answerlist',
      ],
    ];
    $data['questions_answers_question']['answer_count'] = [
      'title' => $this->t('Answer Count'),
      'help' => $this->t('The answer count for this question.'),
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

    // Reported count.
    $data['questions_answers_question']['reported'] = [
      'title' => $this->t('Reported Users'),
      'help' => $this->t('The users reporting this question.'),
      'field' => [
        'id' => 'questions_answers_reported',
      ],
    ];
    $data['questions_answers_question']['reported_count'] = [
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

    // Subscribed count.
    $data['questions_answers_question']['subscribed'] = [
      'title' => $this->t('Subscribed Users'),
      'help' => $this->t('The users subscribing to this question.'),
      'field' => [
        'id' => 'questions_answers_subscribed',
      ],
    ];
    $data['questions_answers_question']['subscribed_count'] = [
      'title' => $this->t('Subscribed Count'),
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

    // Entity.
    $data['questions_answers_question']['entity_id'] = [
      'title' => $this->t('Entity'),
      'help' => $this->t('The entity to which this question is associated.'),
      'field' => [
        'id' => 'questions_answers_parententity',
      ],
    ];

    // User ID.
    $data['questions_answers_question']['uid'] = [
      'title' => $this->t('User ID'),
      'help' => $this->t('ID of the author of this question.'),
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
    $data['questions_answers_question']['author_name'] = [
      'title' => $this->t('Author Name'),
      'help' => $this->t('Name of the author of this question. If you need more information than the name, add the Author relationship.'),
      'field' => [
        'id' => 'questions_answers_author',
      ],
    ];

    // Approval status.
    $data['questions_answers_question']['status'] = [
      'title' => $this->t('Status'),
      'help' => $this->t('The current approval status of the question.'),
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
    $data['questions_answers_question']['created'] = [
      'title' => $this->t('Created'),
      'help' => $this->t('The date the question was submitted.'),
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

    // Relationship to Author User table.
    $data['questions_answers_question']['user_id'] = [
      'title' => $this->t('Question Author'),
      'help' => $this->t('The author of this question'),
      'relationship' => [
        'title' => $this->t('Question Author'),
        'base' => 'users',
        'relationship field' => 'uid',
        'base field' => 'uid',
        'id' => 'standard',
        'handler' => 'views_handler_relationship',
        'label' => $this->t('Question Author'),
      ],
    ];

    // Relationship to Answer table.
    $data['questions_answers_question']['answer_id'] = [
      'title' => $this->t('Answers'),
      'help' => $this->t('The answers to this question'),
      'relationship' => [
        'title' => $this->t('Question Answers'),
        'base' => 'questions_answers_answer',
        'relationship field' => 'id',
        'base field' => 'entity_id',
        'id' => 'standard',
        'handler' => 'views_handler_relationship',
        'label' => $this->t('Question Answers'),
      ],
    ];

    // Remove views that don't make sense.
    unset($data['questions_answers_question']['rendered_entity']);
    unset($data['questions_answers_question']['operations']);

    // Get a field map for questions and answers.
    $fieldmap = $this->entityFieldManager->getFieldMapByFieldType('questions_answers');
    // Get a list of all entities.
    $entities_types = $this->entityManager->getDefinitions();
    foreach ($entities_types as $type => $entity_type) {
      if ($type == 'questions_answers_question' || !$entity_type->entityClassImplements(ContentEntityInterface::class) || !$entity_type->getBaseTable()) {
        continue;
      }
      // Check if the entity is fieldable.
      $entity_type = $this->entityManager->getDefinition($type);
      if (!$entity_type->entityClassImplements(FieldableEntityInterface::class)) {
        continue;
      }

      // Determine if the entity has a questions field.
      if (isset($fieldmap[$type])) {
        // Add a relationship to this entity type.
        $data['questions_answers_question'][$type] = [
          'relationship' => [
            'title' => $entity_type->getLabel(),
            'help' => $this->t('The @entity_type about which the question is being asked.', ['@entity_type' => $entity_type->getLabel()]),
            'base' => $entity_type->getDataTable() ?: $entity_type->getBaseTable(),
            'base field' => $entity_type->getKey('id'),
            'relationship field' => 'entity_id',
            'id' => 'standard',
            'label' => $entity_type->getLabel(),
            'extra' => [
              [
                'field' => 'bundle',
                'value' => $type,
                'table' => 'questions_answers_question',
              ],
            ],
          ],
        ];
      }
    }

    return $data;
  }

}
