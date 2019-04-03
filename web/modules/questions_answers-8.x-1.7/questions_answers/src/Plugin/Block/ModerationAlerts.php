<?php

namespace Drupal\questions_answers\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Provides a 'Moderation Alerts' Block for entity Questions and Answers.
 *
 * @Block(
 *   id = "questions_answers_moderation_alert_block",
 *   admin_label = @Translation("Questions and Answers Moderation Alerts"),
 * )
 */
class ModerationAlerts extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal entity query service container.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryFactory $entity_query) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'answer questions and answers');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block = [
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    // Determine how many questions are awaiting moderation.
    $query = $this->entityQuery->get('questions_answers_question');
    $orCondition = $query->orConditionGroup()
      ->condition('status', 0, '=')
      ->condition('reported_count', 0, '>');
    $count_moderation = $query
      ->condition($orCondition)
      ->count()
      ->execute();
    $query = $this->entityQuery->get('questions_answers_answer');
    $orCondition = $query->orConditionGroup()
      ->condition('status', 0, '=')
      ->condition('reported_count', 0, '>');
    $count_moderation += $query
      ->condition($orCondition)
      ->count()
      ->execute();

    if ($count_moderation > 0) {
      $alert = [
        '#theme' => 'questions_answers_moderation_alert',
        '#message' => Link::createFromRoute($this->t('Alert! @count entity Questions and Answers awaiting moderation.', [
          '@count' => $count_moderation,
        ]), 'questions_answers.moderation_queue'),
      ];
      $block[] = $alert;
    }

    return $block;
  }

}
