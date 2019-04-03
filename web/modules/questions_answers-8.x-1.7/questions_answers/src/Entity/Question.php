<?php

namespace Drupal\questions_answers\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\questions_answers\QuestionInterface;

/**
 * Defines the Question entity.
 *
 * @ingroup questions_answers
 *
 * @ContentEntityType(
 *   id = "questions_answers_question",
 *   label = @Translation("Question"),
 *   base_table = "questions_answers_question",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "question",
 *     "published" = "status",
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\questions_answers\QuestionViewsData",
 *   },
 * )
 */
class Question extends ContentEntityBase implements QuestionInterface {

  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    $user = $this->get('uid')->entity;
    if (!$user || $user->isAnonymous()) {
      $user = User::getAnonymousUser();
      $user->name = $this->getAuthorName();
    }
    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorName() {
    if ($this->get('uid')->target_id) {
      return $this->get('uid')->entity->label();
    }
    return \Drupal::config('user.settings')->get('anonymous');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published = NULL) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    $this->save();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function checkViewable() {
    if (\Drupal::currentUser()->hasPermission('administer questions and answers')) {
      return TRUE;
    }
    else {
      return $this->isPublished() == 1;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addReport($user_id) {
    // Get the current reports.
    $reports = $this->getReports();
    // Add this user.
    $reports[] = $user_id;
    $this->reported = [$reports];
    $this->reported_count = count($reports);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getReports() {
    $reports = $this->reported->first();
    if (!empty($reports)) {
      return $reports->getValue();
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getReportCount() {
    return $this->reported_count->value;
  }

  /**
   * {@inheritdoc}
   */
  public function clearReports() {
    $this->reported = [[]];
    $this->reported_count = 0;
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function addSubscription($user_id) {
    // Remove the subscription if it already exists.
    $this->removeSubscription($user_id);
    // Get the current reports.
    $subscribed = $this->getSubscribed();
    // Add this user.
    $subscribed[] = $user_id;
    $this->subscribed = [$subscribed];
    $this->subscribed_count = count($subscribed);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function removeSubscription($user_id) {
    // Get the current reports.
    $subscribed = $this->getSubscribed();
    // Remove the user.
    if (($key = array_search(strtolower($user_id), array_map('strtolower', $subscribed))) !== FALSE) {
      unset($subscribed[$key]);
    }
    $this->subscribed = [$subscribed];
    $this->subscribed_count = count($subscribed);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getSubscribed() {
    $subscriptions = $this->subscribed->first();
    if (!empty($subscriptions)) {
      return $subscriptions->getValue();
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSubscribedCount() {
    return $this->subscribed_count->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    if (isset($this->get('created')->value)) {
      return $this->get('created')->value;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->get('question')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value) {
    $this->question->value = $value;
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function updateAnswerCount() {
    $answers = $this->getAnswers();
    $this->answer_count->value = count($answers);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswerCount() {
    return $this->answer_count->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswers() {
    $answer_ids = \Drupal::entityQuery('questions_answers_answer')
      ->condition('entity_id', $this->id(), '=')
      ->execute();
    return Answer::loadMultiple($answer_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return \Drupal::entityTypeManager()->getStorage($this->get('bundle')->value)->load($this->get('entity_id')->entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Question entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Question entity.'))
      ->setReadOnly(TRUE);

    // We have to use bundle as well because this refers to any entity type.
    $fields['entity_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The ID of the associated entity for the question.'))
      ->setTranslatable(TRUE)
      ->setReadOnly(TRUE);

    $fields['bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Bundle'))
      ->setDescription(t('The bundle of the associated entity for the question.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ]);

    $fields['question'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Question'))
      ->setDescription(t('The text of the Question entity.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => '',
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['reported'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Reported'))
      ->setDescription(t('A list of users who have reported this question.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setDefaultValue([]);
    $fields['reported_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Reported Count'))
      ->setDescription(t('The current count of reports.'))
      ->setReadOnly(TRUE)
      ->setDefaultValue(0);

    $fields['subscribed'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Subscribed'))
      ->setDescription(t('A list of users who have subscribed to this question.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setDefaultValue([]);
    $fields['subscribed_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Subscribed Count'))
      ->setDescription(t('The current count of subscriptions.'))
      ->setReadOnly(TRUE)
      ->setDefaultValue(0);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the question author.'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValue(0);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setTranslatable(TRUE);

    $fields['answer_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Answer Count'))
      ->setDescription(t('The current count of answers.'))
      ->setReadOnly(TRUE)
      ->setDefaultValue(0);

    // Set the default value callback for the status field.
    $fields['status']->setDefaultValueCallback('Drupal\questions_answers\Entity\Question::getDefaultStatus');

    return $fields;
  }

  /**
   * Default value callback for 'status' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return bool
   *   TRUE if the comment should be published, FALSE otherwise.
   */
  public static function getDefaultStatus() {
    return \Drupal::currentUser()->hasPermission('administer questions and answers') ? QuestionInterface::PUBLISHED : QuestionInterface::NOT_PUBLISHED;
  }

}
